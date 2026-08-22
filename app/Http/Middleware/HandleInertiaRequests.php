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
            'settings' => fn () => cache()->remember('site_settings', 300, function () {
                return [
                    'site_name'          => \App\Models\Setting::get('site_name', config('app.name', 'Chamse')),
                    'site_description'   => \App\Models\Setting::get('site_description'),
                    'logo'               => \App\Models\Setting::get('logo'),
                    'favicon'            => \App\Models\Setting::get('favicon'),
                    'primary_color'      => \App\Models\Setting::get('primary_color', '#2563EB'),
                    'secondary_color'    => \App\Models\Setting::get('secondary_color', '#8b5cf6'),
                    'accent_color'       => \App\Models\Setting::get('accent_color', '#f59e0b'),
                    'theme_mode'         => \App\Models\Setting::get('theme_mode', 'light'),
                    'footer_text'        => \App\Models\Setting::get('footer_text'),
                    'contact_phone'      => \App\Models\Setting::get('contact_phone'),
                    'contact_email'      => \App\Models\Setting::get('contact_email'),
                    'contact_address'    => \App\Models\Setting::get('contact_address'),
                    'currency_symbol'    => \App\Models\Setting::get('currency_symbol', 'F CFA'),
                    'social_whatsapp'         => \App\Models\Setting::get('social_whatsapp'),
                    'social_facebook'         => \App\Models\Setting::get('social_facebook'),
                    'social_instagram'        => \App\Models\Setting::get('social_instagram'),
                    'whatsapp_order_enabled'  => \App\Models\Setting::get('whatsapp_order_enabled', '1'),
                ];
            }),

            // Top categories for nav dropdown
            'nav_categories' => fn () => cache()->remember('nav_categories', 1800, function () {
                return \App\Models\Category::active()
                    ->roots()
                    ->ordered()
                    ->take(8)
                    ->get()
                    ->map(fn($c) => [
                        'id'    => $c->id,
                        'name'  => $c->name,
                        'slug'  => $c->slug,
                        'image' => $c->image,
                    ])
                    ->toArray();
            }),

            // Cart count (depuis la table carts en DB)
            'cart_count' => fn () => \App\Models\Cart::where('session_id', session()->getId())
                ->withSum('items', 'quantity')
                ->first()
                ?->items_sum_quantity ?? 0,

            // Bannières globales (announcement_bar + popup_center) — toutes les pages
            'banners' => fn () => cache()->remember('banners_global', 120, function () {
                $fmt = fn($b) => [
                    'id'               => $b->id,
                    'title'            => $b->title,
                    'subtitle'         => $b->subtitle,
                    'description'      => $b->description,
                    'link'             => $b->link,
                    'button_text'      => $b->button_text,
                    'image_url'        => $b->image_url,
                    'background_color' => $b->background_color,
                    'text_color'       => $b->text_color,
                ];
                return [
                    'announcement' => \App\Models\Banner::active()
                        ->position('announcement_bar')
                        ->orderBy('order')
                        ->get()
                        ->map($fmt)
                        ->values()
                        ->all(),
                    'popup' => ($p = \App\Models\Banner::active()->position('popup_center')->orderBy('order')->first())
                        ? $fmt($p)
                        : null,
                ];
            }),
        ]);
    }
}
