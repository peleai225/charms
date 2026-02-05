<?php

namespace App\Listeners;

use App\Events\StockUpdated;
use App\Mail\LowStockAlert;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            $this->sendLowStockAlert($product, $variant, $currentStock, $threshold);
        }

        // Vérifier si rupture de stock
        if ($currentStock <= 0) {
            $this->sendOutOfStockAlert($product, $variant, $threshold);
        }
    }

    protected function sendLowStockAlert($product, $variant, $currentStock, $threshold): void
    {
        $productName = $variant 
            ? "{$product->name} - {$variant->name}" 
            : $product->name;

        ActivityLog::log(
            'low_stock_alert',
            "Stock bas : {$productName} - {$currentStock} restant(s)",
            $product
        );

        Log::warning("Stock bas pour {$productName}: {$currentStock} unités restantes");

        // Envoyer un email à l'admin
        $this->sendEmail($product, $variant, $currentStock, $threshold, false);
    }

    protected function sendOutOfStockAlert($product, $variant, $threshold): void
    {
        $productName = $variant 
            ? "{$product->name} - {$variant->name}" 
            : $product->name;

        ActivityLog::log(
            'out_of_stock_alert',
            "Rupture de stock : {$productName}",
            $product
        );

        Log::error("Rupture de stock pour {$productName}");

        // Envoyer un email à l'admin
        $this->sendEmail($product, $variant, 0, $threshold, true);
    }

    protected function sendEmail($product, $variant, $currentStock, $threshold, $isOutOfStock): void
    {
        try {
            // Récupérer l'email admin depuis les settings ou les utilisateurs admin
            $adminEmail = Setting::get('admin_email');
            
            if (!$adminEmail) {
                // Fallback : prendre le premier admin
                $admin = User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'manager']);
                })->first();
                
                if ($admin) {
                    $adminEmail = $admin->email;
                }
            }

            if (!$adminEmail) {
                // Fallback final : email depuis config
                $adminEmail = config('mail.from.address');
            }

            if ($adminEmail) {
                // Configurer la connexion mail depuis les paramètres
                MailConfigService::configureFromSettings();
                
                Mail::to($adminEmail)->send(
                    new LowStockAlert($product, $variant, $currentStock, $threshold, $isOutOfStock)
                );
            }
        } catch (\Exception $e) {
            Log::error('Erreur envoi email alerte stock', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

