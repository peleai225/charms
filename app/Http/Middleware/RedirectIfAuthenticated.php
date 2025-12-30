<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Redirige si déjà authentifié
     */
    public function handle(Request $request, Closure $next, string $guard = null): Response
    {
        if (auth()->guard($guard)->check()) {
            $user = auth()->user();
            
            // Rediriger selon le rôle
            if (in_array($user->role, ['admin', 'manager', 'staff'])) {
                return redirect()->route('admin.dashboard');
            }
            
            return redirect()->route('home');
        }

        return $next($request);
    }
}

