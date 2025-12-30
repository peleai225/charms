<?php

namespace App\Console\Commands;

use App\Events\OrderCancelled;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-expired {--minutes=30 : Minutes avant expiration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Annule les commandes en attente de paiement depuis plus de X minutes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $expiredAt = now()->subMinutes($minutes);

        $this->info("Recherche des commandes en attente de paiement depuis plus de {$minutes} minutes...");

        // Récupérer les commandes expirées
        $expiredOrders = Order::where('payment_status', 'pending')
            ->where('status', 'pending')
            ->where('created_at', '<', $expiredAt)
            ->whereIn('payment_method', ['cinetpay', 'card', 'mobile_money'])
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('Aucune commande expirée trouvée.');
            return Command::SUCCESS;
        }

        $this->info("Trouvé {$expiredOrders->count()} commande(s) à annuler.");

        $bar = $this->output->createProgressBar($expiredOrders->count());
        $bar->start();

        $cancelled = 0;

        foreach ($expiredOrders as $order) {
            try {
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'expired',
                    'notes' => ($order->notes ? $order->notes . "\n" : '') . 
                               "Annulée automatiquement: paiement non reçu après {$minutes} minutes",
                ]);

                // Déclencher l'événement d'annulation (pour restaurer le stock si nécessaire)
                event(new OrderCancelled($order, 'Paiement expiré'));

                Log::info('Order cancelled due to payment timeout', [
                    'order_number' => $order->order_number,
                    'created_at' => $order->created_at,
                ]);

                $cancelled++;
            } catch (\Exception $e) {
                Log::error('Failed to cancel expired order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Erreur pour la commande {$order->order_number}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("{$cancelled} commande(s) annulée(s) avec succès.");

        return Command::SUCCESS;
    }
}
