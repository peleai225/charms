<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPassword;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion client
     */
    public function showLoginForm()
    {
        return view('front.auth.login');
    }

    /**
     * Affiche le formulaire d'inscription client
     */
    public function showRegisterForm()
    {
        return view('front.auth.register');
    }

    /**
     * Traite la connexion client
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Ces identifiants sont incorrects.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Ce compte a été désactivé. Contactez le support.'],
            ]);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Ces identifiants sont incorrects.'],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        // Mettre à jour la date de dernière connexion
        $user->update(['last_login_at' => now()]);

        // Associer le panier de session au client
        $this->mergeSessionCart($user);

        $request->session()->regenerate();

        // Rediriger vers l'URL prévue ou l'accueil
        return redirect()->intended(route('home'));
    }

    /**
     * Traite l'inscription client
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'phone' => ['nullable', 'string', 'max:20'],
            'newsletter' => ['nullable', 'boolean'],
            'terms' => ['required', 'accepted'],
        ], [
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'terms.accepted' => 'Vous devez accepter les conditions générales.',
        ]);

        DB::beginTransaction();

        try {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'role' => 'customer',
                'is_active' => true,
            ]);

            // Créer le profil client
            Customer::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'newsletter' => $validated['newsletter'] ?? false,
                'accepts_marketing' => $validated['newsletter'] ?? false,
            ]);

            DB::commit();

            // Connecter l'utilisateur
            Auth::login($user);

            // Log
            ActivityLog::log('register', 'Inscription client', $user);

            // Associer le panier de session
            $this->mergeSessionCart($user);

            return redirect()->route('home')->with('success', 'Bienvenue ! Votre compte a été créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Déconnexion client
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Vous avez été déconnecté.');
    }

    /**
     * Affiche le formulaire de mot de passe oublié
     */
    public function showForgotPasswordForm()
    {
        return view('front.auth.forgot-password');
    }

    /**
     * Envoie le lien de réinitialisation
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Vérifier si l'utilisateur existe et est un client
        $user = User::where('email', $request->email)
            ->where('role', 'customer')
            ->where('is_active', true)
            ->first();

        if (!$user) {
            // Pour la sécurité, on ne révèle pas si l'email existe ou non
            return back()->with('success', 'Si cette adresse email existe, vous recevrez un lien de réinitialisation.');
        }

        // Générer un token de réinitialisation
        $token = Str::random(64);
        
        // Sauvegarder le token dans la table password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Envoyer l'email avec le lien de réinitialisation
        try {
            Mail::to($user->email)->send(new ResetPassword($token, $user->email));
            
            ActivityLog::log('password_reset_requested', 'Demande de réinitialisation de mot de passe', $user);
            
            return back()->with('success', 'Si cette adresse email existe, vous recevrez un lien de réinitialisation.');
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email réinitialisation mot de passe: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue. Veuillez réessayer plus tard.');
        }
    }

    /**
     * Affiche le formulaire de réinitialisation
     */
    public function showResetForm(Request $request, string $token)
    {
        $email = $request->query('email');
        
        return view('front.auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Traite la réinitialisation du mot de passe
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        // Vérifier le token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Ce lien de réinitialisation est invalide ou a expiré.']);
        }

        // Vérifier si le token est valide (60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Ce lien de réinitialisation a expiré. Veuillez en demander un nouveau.']);
        }

        // Vérifier le token
        if (!Hash::check($request->token, $resetRecord->token)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Ce lien de réinitialisation est invalide.']);
        }

        // Trouver l'utilisateur
        $user = User::where('email', $request->email)
            ->where('role', 'customer')
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Aucun compte trouvé avec cette adresse email.']);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Supprimer le token utilisé
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Log
        ActivityLog::log('password_reset', 'Mot de passe réinitialisé', $user);

        return redirect()->route('login')
            ->with('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }

    /**
     * Fusionne le panier de session avec le panier du client
     */
    protected function mergeSessionCart(User $user): void
    {
        $customer = Customer::where('user_id', $user->id)->first();
        
        if (!$customer) {
            return;
        }

        $sessionId = session()->getId();
        
        // Trouver le panier de session
        $sessionCart = \App\Models\Cart::where('session_id', $sessionId)
            ->whereNull('customer_id')
            ->first();

        if ($sessionCart && $sessionCart->items->isNotEmpty()) {
            // Trouver ou créer le panier du client
            $customerCart = \App\Models\Cart::firstOrCreate(
                ['customer_id' => $customer->id],
                ['session_id' => $sessionId]
            );

            // Fusionner les items
            foreach ($sessionCart->items as $item) {
                $existingItem = $customerCart->items()
                    ->where('product_id', $item->product_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->first();

                if ($existingItem) {
                    $existingItem->increment('quantity', $item->quantity);
                } else {
                    $customerCart->items()->create([
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                    ]);
                }
            }

            // Supprimer le panier de session
            $sessionCart->delete();
        }
    }
}

