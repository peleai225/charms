<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Vérifie si l'utilisateur a un rôle admin (admin, manager, staff)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login')->with('error', 'Veuillez vous connecter.');
        }

        $user = auth()->user();

        // Si pas de rôles spécifiés, accepter admin, manager, staff
        if (empty($roles)) {
            $roles = ['admin', 'manager', 'staff'];
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'Accès non autorisé.');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('admin.login')->with('error', 'Votre compte a été désactivé.');
        }

        return $next($request);
    }
}

