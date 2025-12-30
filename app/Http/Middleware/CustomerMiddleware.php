<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    /**
     * Vérifie si l'utilisateur est un client authentifié
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            // Sauvegarder l'URL de destination
            session()->put('url.intended', url()->current());
            
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = auth()->user();

        // Vérifier si l'utilisateur est actif (si la propriété existe)
        if (property_exists($user, 'is_active') && !$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Votre compte a été désactivé.');
        }

        return $next($request);
    }
}

