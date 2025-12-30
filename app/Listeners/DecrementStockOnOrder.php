<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Events\StockUpdated;
use App\Models\StockMovement;

class DecrementStockOnOrder
{
    /**
     * Décrémente le stock APRÈS confirmation du paiement
     * Sécurité: Le stock n'est pas décrémenté à la création de commande,
     * mais uniquement quand le paiement est confirmé via webhook CinetPay
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;

        foreach ($order->items as $item) {
            if (!$item->product || !$item->product->track_stock) {
                continue;
            }

            $movement = StockMovement::createMovement(
                product: $item->product,
                type: StockMovement::TYPE_SALE,
                quantity: -$item->quantity, // Négatif pour sortie
                variant: $item->productVariant,
                unitPrice: $item->unit_price,
                reference: $order,
                notes: "Commande #{$order->order_number}"
            );

            // Déclencher l'événement de mise à jour du stock
            StockUpdated::dispatch($item->product, $item->productVariant, $movement);
        }
    }
}

