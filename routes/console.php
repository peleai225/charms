<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Planification des tâches
|--------------------------------------------------------------------------
|
| Pour activer la planification, ajoutez cette entrée cron sur votre serveur:
| * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Annuler les commandes en attente de paiement depuis plus de 30 minutes
Schedule::command('orders:cancel-expired --minutes=30')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/expired-orders.log'));
