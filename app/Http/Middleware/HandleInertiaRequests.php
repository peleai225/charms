<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // Auth user data
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role ?? 'customer',
                ] : null,
            ],

            // Flash messages
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],

            // Validation errors
            'errors' => fn () => $request->session()->get('errors')
                ? $request->session()->get('errors')->getBag('default')->getMessages()
                : (object) [],

            // Site settings (cached)
            'settings' => fn () => cache()->remember('site_settings', 3600, function () {
                return [
                    'site_name' => \App\Models\Setting::get('site_name', config('app.name', 'Chamse')),
                    'logo' => \App\Models\Setting::get('logo'),
                    'favicon' => \App\Models\Setting::get('favicon'),
                    'primary_color' => \App\Models\Setting::get('primary_color', '#2563EB'),
                ];
            }),

            // Cart count
            'cart_count' => fn () => session()->has('cart')
                ? collect(session('cart'))->sum('quantity')
                : 0,
        ]);
    }
}
