<?php

namespace App\Listeners;

use App\Events\OrderRefunded;
use App\Events\StockUpdated;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Log;

class RestoreStockOnRefund
{
    /**
     * Restaure le stock lors d'un remboursement
     * Note: On restaure uniquement si le produit a été retourné physiquement
     */
    public function handle(OrderRefunded $event): void
    {
        $order = $event->order;
        $refund = $event->refund;

        // Par défaut, on restaure le stock pour tous les items
        // Vous pouvez ajouter une logique pour vérifier si les produits ont été retournés
        foreach ($order->items as $item) {
            if (!$item->product || !$item->product->track_stock) {
                continue;
            }

            // Calculer la quantité remboursée (proportionnel ou total selon la logique métier)
            // Ici, on assume que le remboursement concerne tous les items
            // Vous pouvez adapter selon votre logique (remboursement partiel par item)
            $quantityToRestore = $item->quantity;

            // Si l'item a déjà été partiellement remboursé, ajuster
            if ($item->quantity_refunded > 0) {
                $quantityToRestore = $item->quantity - $item->quantity_refunded;
            }

            if ($quantityToRestore <= 0) {
                continue;
            }

            try {
                $movement = StockMovement::createMovement(
                    product: $item->product,
                    type: StockMovement::TYPE_RETURN_IN,
                    quantity: $quantityToRestore,
                    variant: $item->productVariant,
                    unitPrice: $item->unit_price,
                    reference: $order,
                    notes: "Remboursement #{$refund->refund_number} - Commande #{$order->order_number}" . ($refund->notes ? " - {$refund->notes}" : '')
                );

                // Mettre à jour la quantité remboursée de l'item
                $item->increment('quantity_refunded', $quantityToRestore);

                StockUpdated::dispatch($item->product, $item->productVariant, $movement);
            } catch (\Exception $e) {
                Log::error('Erreur restauration stock lors remboursement', [
                    'order_id' => $order->id,
                    'refund_id' => $refund->id,
                    'item_id' => $item->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
