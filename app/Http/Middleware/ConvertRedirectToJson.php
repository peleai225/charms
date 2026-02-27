<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConvertRedirectToJson
{
    /**
     * Convertit les RedirectResponse en JSON quand la requête est AJAX,
     * pour permettre les mises à jour sans rechargement.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$response instanceof RedirectResponse) {
            return $response;
        }

        if (!$request->expectsJson() && !$request->ajax()) {
            return $response;
        }

        $message = $request->session()->get('success') ?? $request->session()->get('error') ?? $request->session()->get('warning');
        $type = $request->session()->get('success') ? 'success' : ($request->session()->get('error') ? 'error' : 'warning');

        $errors = $request->session()->get('errors');
        if ($errors) {
            $message = $errors->first() ?? $message;
            $type = 'error';
        }

        return response()->json([
            'redirect' => $response->getTargetUrl(),
            'message' => $message,
            'type' => $type,
            'errors' => $errors ? $errors->getMessages() : null,
        ]);
    }
}
