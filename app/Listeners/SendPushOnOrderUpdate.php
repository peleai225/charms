<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class SendPushOnOrderUpdate
{
    protected PushNotificationService $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    public function handle(object $event): void
    {
        $order = $event->order;
        if (!$order->customer_id) return;

        try {
            match (true) {
                $event instanceof OrderCreated => $this->pushService->sendToCustomer(
                    $order->customer_id,
                    'Commande recue !',
                    "Votre commande {$order->order_number} a bien ete enregistree.",
                    route('account.orders.show', $order)
                ),
                $event instanceof OrderPaid => $this->pushService->sendToCustomer(
                    $order->customer_id,
                    'Paiement confirme',
                    "Le paiement de votre commande {$order->order_number} a ete confirme.",
                    route('account.orders.show', $order)
                ),
                $event instanceof OrderCancelled => $this->pushService->sendToCustomer(
                    $order->customer_id,
                    'Commande annulee',
                    "Votre commande {$order->order_number} a ete annulee.",
                    route('account.orders')
                ),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::error('Push notification failed: ' . $e->getMessage());
        }
    }
}
