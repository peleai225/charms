<?php

namespace App\Listeners;

use App\Events\OrderPaid;

class UpdateCustomerStats
{
    /**
     * Met à jour les statistiques du client après paiement
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;

        if ($order->customer) {
            $order->customer->updateStats();
        }
    }
}

