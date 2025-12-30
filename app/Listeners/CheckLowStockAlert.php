<?php

namespace App\Listeners;

use App\Events\StockUpdated;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class CheckLowStockAlert
{
    /**
     * Vérifie si le stock est bas et envoie une alerte
     */
    public function handle(StockUpdated $event): void
    {
        $product = $event->product;
        $variant = $event->variant;

        // Déterminer le stock actuel
        $currentStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
        $threshold = $variant?->stock_alert_threshold ?? $product->stock_alert_threshold;

        // Vérifier si on est passé sous le seuil
        if ($currentStock <= $threshold && $currentStock > 0) {
            $this->sendLowStockAlert($product, $variant, $currentStock);
        }

        // Vérifier si rupture de stock
        if ($currentStock <= 0) {
            $this->sendOutOfStockAlert($product, $variant);
        }
    }

    protected function sendLowStockAlert($product, $variant, $currentStock): void
    {
        $productName = $variant 
            ? "{$product->name} - {$variant->name}" 
            : $product->name;

        ActivityLog::log(
            'low_stock_alert',
            "Stock bas : {$productName} - {$currentStock} restant(s)",
            $product
        );

        // TODO: Envoyer un email à l'admin
        Log::warning("Stock bas pour {$productName}: {$currentStock} unités restantes");
    }

    protected function sendOutOfStockAlert($product, $variant): void
    {
        $productName = $variant 
            ? "{$product->name} - {$variant->name}" 
            : $product->name;

        ActivityLog::log(
            'out_of_stock_alert',
            "Rupture de stock : {$productName}",
            $product
        );

        // TODO: Envoyer un email à l'admin
        Log::error("Rupture de stock pour {$productName}");
    }
}

