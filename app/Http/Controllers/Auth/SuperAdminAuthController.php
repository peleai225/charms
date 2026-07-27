<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SuperAdminAuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion superadmin
     */
    public function showLoginForm()
    {
        return view('superadmin.auth.login');
    }

    /**
     * Traite la connexion superadmin
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Vérifier si l'utilisateur existe et a le rôle superadmin
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || $user->role !== 'superadmin') {
            throw ValidationException::withMessages([
                'email' => ['Ces identifiants ne correspondent pas à un compte super administrateur.'],
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
        ActivityLog::log('login', 'Connexion superadmin', $user);

        $request->session()->regenerate();

        return redirect()->intended(route('superadmin.dashboard'));
    }

    /**
     * Déconnexion superadmin
     */
    public function logout(Request $request)
    {
        ActivityLog::log('logout', 'Déconnexion superadmin');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login')->with('success', 'Vous avez été déconnecté.');
    }
}
