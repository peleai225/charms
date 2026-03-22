<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\LoyaltyTransaction;
use App\Models\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AwardLoyaltyPointsOnPayment implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    /**
     * Attribue des points de fidélité après un paiement confirmé.
     *
     * Taux par défaut : 1 point par 100 F CFA dépensés (configurable).
     */
    public function handle(OrderPaid $event): void
    {
        $order    = $event->order;
        $customer = $order->customer;

        if (!$customer) {
            return;
        }

        // Taux configuré dans les paramètres (points pour 1000 F)
        $pointsPer1000 = (int) Setting::get('loyalty_points_per_1000', 10);

        if ($pointsPer1000 <= 0) {
            return;
        }

        $points = (int) floor($order->total / 1000 * $pointsPer1000);

        if ($points <= 0) {
            return;
        }

        $customer->addLoyaltyPoints($points);
        $customer->refresh();

        LoyaltyTransaction::create([
            'customer_id'   => $customer->id,
            'order_id'      => $order->id,
            'type'          => 'earn',
            'points'        => $points,
            'balance_after' => $customer->loyalty_points,
            'description'   => "Commande #{$order->order_number} — {$points} points gagnés",
        ]);
    }
}
