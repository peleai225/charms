<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\OrderInvoice;
use App\Services\MailConfigService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceOnPayment
{
    use InteractsWithQueue;

    /** Nombre de tentatives en cas d'échec */
    public int $tries = 3;

    /** Délai entre tentatives (secondes) */
    public int $backoff = 60;

    /**
     * Envoie la facture par email après confirmation du paiement
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;
        
        // Envoyer la facture uniquement si l'email est disponible
        if ($order->billing_email) {
            try {
                // Configurer la connexion mail depuis les paramètres
                MailConfigService::configureFromSettings();
                
                Mail::to($order->billing_email)->send(new OrderInvoice($order));
                Log::info("Facture envoyée par email", [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'email' => $order->billing_email,
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur envoi facture par email', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
