<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Events\StockUpdated;
use App\Models\StockMovement;

class RestoreStockOnCancel
{
    /**
     * Restaure le stock lors de l'annulation de commande
     */
    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        foreach ($order->items as $item) {
            if (!$item->product || !$item->product->track_stock) {
                continue;
            }

            $movement = StockMovement::createMovement(
                product: $item->product,
                type: StockMovement::TYPE_RETURN_IN,
                quantity: $item->quantity, // Positif pour entrée
                variant: $item->productVariant,
                unitPrice: $item->unit_price,
                reference: $order,
                notes: "Annulation commande #{$order->order_number}" . ($event->reason ? " - {$event->reason}" : '')
            );

            StockUpdated::dispatch($item->product, $item->productVariant, $movement);
        }
    }
}

