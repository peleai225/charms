<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('superadmin.login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        // Vérifier si l'utilisateur a le rôle superadmin
        if (Auth::user()->role !== 'superadmin') {
            Auth::logout();
            return redirect()->route('superadmin.login')->with('error', 'Accès refusé. Cet espace est réservé aux super administrateurs.');
        }

        // Vérifier si le compte est actif
        if (!Auth::user()->is_active) {
            Auth::logout();
            return redirect()->route('superadmin.login')->with('error', 'Votre compte a été désactivé.');
        }

        return $next($request);
    }
}
