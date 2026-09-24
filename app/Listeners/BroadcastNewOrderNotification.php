<?php

namespace App\Listeners;

use App\Events\NewOrderNotification;
use App\Events\OrderCreated;

class BroadcastNewOrderNotification
{
    public function handle(OrderCreated $event): void
    {
        try {
            NewOrderNotification::dispatch($event->order);
        } catch (\Exception $e) {
            \Log::warning('BroadcastNewOrderNotification failed: ' . $e->getMessage());
        }
    }
}
