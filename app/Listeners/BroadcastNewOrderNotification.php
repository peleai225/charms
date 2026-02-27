<?php

namespace App\Listeners;

use App\Events\NewOrderNotification;
use App\Events\OrderCreated;

class BroadcastNewOrderNotification
{
    /**
     * Diffuser la notification en temps réel aux admins
     */
    public function handle(OrderCreated $event): void
    {
        NewOrderNotification::dispatch($event->order);
    }
}
