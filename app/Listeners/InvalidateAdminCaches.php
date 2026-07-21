<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;

class InvalidateAdminCaches
{
    public function handle(mixed $event): void
    {
        Cache::forget('admin_pending_orders_count');
        Cache::forget('admin_stock_alerts_count');
    }
}
