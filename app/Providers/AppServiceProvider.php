<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Utiliser la vue de pagination Tailwind personnalisée (mobile-friendly)
        Paginator::useTailwind();

        // Locale française par défaut
        \Carbon\Carbon::setLocale(config('app.locale', 'fr'));
    }
}
