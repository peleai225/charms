<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCartMail;
use App\Models\Cart;
use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartReminders extends Command
{
    protected $signature = 'carts:send-reminders
                            {--minutes=60 : Délai en minutes avant de considérer un panier abandonné}';

    protected $description = 'Envoie un email de rappel aux clients qui ont un panier non finalisé';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $cutoff  = now()->subMinutes($minutes);

        // Paniers avec articles, non liés à une commande, pas encore relancés,
        // inactifs depuis $minutes minutes, et appartenant à un client avec email
        $carts = Cart::query()
            ->whereHas('items')
            ->whereNotNull('customer_id')
            ->whereNull('reminder_sent_at')
            ->where('updated_at', '<=', $cutoff)
            ->whereHas('customer', fn($q) => $q->whereNotNull('email'))
            ->with(['items.product.images', 'items.variant', 'customer'])
            ->get();

        $sent = 0;

        foreach ($carts as $cart) {
            /** @var Customer $customer */
            $customer = $cart->customer;

            // Ne pas relancer si le client a déjà passé une commande récemment
            $hasRecentOrder = $customer->orders()
                ->where('created_at', '>=', $cutoff)
                ->exists();

            if ($hasRecentOrder) {
                // Marquer le panier comme traité sans envoyer l'email
                $cart->update(['reminder_sent_at' => now()]);
                continue;
            }

            try {
                Mail::to($customer->email, $customer->full_name)
                    ->send(new AbandonedCartMail($cart, $customer));

                $cart->update([
                    'abandoned_at'     => $cart->updated_at,
                    'reminder_sent_at' => now(),
                ]);

                $sent++;
                $this->line("Email envoyé à {$customer->email} (panier #{$cart->id})");

            } catch (\Exception $e) {
                $this->error("Erreur pour {$customer->email} : " . $e->getMessage());
            }
        }

        $this->info("{$sent} email(s) de relance envoyé(s).");

        return self::SUCCESS;
    }
}
