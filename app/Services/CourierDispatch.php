<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Courier;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

/**
 * Remettre des commandes à un livreur, ou les lui reprendre avant son départ.
 * Aucun message n'est envoyé aux clients.
 */
class CourierDispatch
{
    private const DISPATCHABLE = [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING];

    public function __construct(private ShippingZones $zones) {}

    /**
     * @param  array<int>  $orderIds
     * @return array{dispatched: int, skipped: int}
     */
    public function dispatch(Courier $courier, array $orderIds): array
    {
        if (! $courier->is_active) {
            throw ValidationException::withMessages(['courier' => "Ce livreur est inactif : réactivez-le avant de lui confier des commandes."]);
        }

        $dispatched = 0;
        $skipped = 0;

        foreach (Order::whereIn('id', $orderIds)->get() as $order) {
            if (! in_array($order->status, self::DISPATCHABLE, true) || $order->courier_id !== null) {
                $skipped++;

                continue;
            }

            $order->update([
                'status' => Order::STATUS_DELIVERY_IN_PROGRESS,
                'courier_id' => $courier->id,
                'dispatched_at' => now(),
                // Copié à la remise : un changement de tarif ensuite ne modifie pas cette livraison
                'courier_fee' => $this->zones->courierFeeForCity($order->shipping_city),
            ]);
            ActivityLog::log('order_dispatched', "Confiée au livreur {$courier->name}", $order);
            $dispatched++;
        }

        return ['dispatched' => $dispatched, 'skipped' => $skipped];
    }

    public function withdraw(Order $order): void
    {
        if ($order->status !== Order::STATUS_DELIVERY_IN_PROGRESS) {
            throw ValidationException::withMessages(['order' => "Cette commande n'est pas chez un livreur."]);
        }

        $order->update([
            'status' => Order::STATUS_PROCESSING,
            'courier_id' => null,
            'dispatched_at' => null,
            'courier_fee' => null,
        ]);
        ActivityLog::log('order_withdrawn', 'Reprise au livreur avant son départ', $order);
    }
}
