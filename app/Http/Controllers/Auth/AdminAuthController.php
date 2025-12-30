<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion admin
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Traite la connexion admin
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Vérifier si l'utilisateur existe et a un rôle admin
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !in_array($user->role, ['admin', 'manager', 'staff'])) {
            throw ValidationException::withMessages([
                'email' => ['Ces identifiants ne correspondent pas à un compte administrateur.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Ce compte a été désactivé.'],
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

        // Log de l'activité
        ActivityLog::log('login', 'Connexion admin', $user);

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Déconnexion admin
     */
    public function logout(Request $request)
    {
        ActivityLog::log('logout', 'Déconnexion admin');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Vous avez été déconnecté.');
    }
}

