<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\ThermalPrinterService;

class PrintReceipt extends Command
{
    protected $signature = 'receipt:print
                            {order : ID de la commande}
                            {--printer= : Nom imprimante (défaut: config pos.printer_name)}
                            {--width=48 : Largeur en caractères (48=80mm, 32=58mm)}
                            {--text : Afficher en texte au lieu d\'imprimer}';

    protected $description = 'Imprimer un reçu de vente sur imprimante thermique POS';

    public function handle(ThermalPrinterService $service)
    {
        $orderId = $this->argument('order');
        $order = Order::find($orderId);

        if (!$order) {
            $this->error("❌ Commande #{$orderId} introuvable");
            return 1;
        }

        if ($order->source !== 'pos') {
            $this->warn("⚠️  Cette commande n'est pas une vente POS (source: {$order->source})");
            if (!$this->confirm('Continuer quand même ?')) {
                return 0;
            }
        }

        $this->info("📄 Génération du reçu pour commande {$order->order_number}...");

        $options = [
            'width' => (int) $this->option('width'),
            'order_url' => route('front.account.orders.show', $order),
        ];

        $receiptData = $service->generateReceipt($order, $options);

        // Mode texte
        if ($this->option('text')) {
            $plainText = $service->toPlainText($receiptData);
            $this->line('');
            $this->line($plainText);
            $this->line('');
            $this->info('✅ Reçu généré en mode texte');
            return 0;
        }

        // Mode impression
        $printerName = $this->option('printer') ?? config('pos.printer_name');

        if (!$printerName) {
            $this->error("❌ Nom d'imprimante non configuré");
            $this->line("Définissez POS_PRINTER_NAME dans .env ou utilisez --printer=NOM");
            return 1;
        }

        $this->info("🖨️  Impression sur '{$printerName}'...");

        $error = $service->printDirectly($receiptData, $printerName);

        if ($error) {
            $this->error("❌ Échec: $error");
            $this->line('');
            $this->line('💡 Vérifiez que:');
            $this->line('  - L\'imprimante est allumée et connectée');
            $this->line('  - Le nom est exact (Windows: voir Périphériques et imprimantes)');
            $this->line('  - Les drivers ESC/POS sont installés');
            $this->line('');
            $this->line('Pour tester en mode texte: php artisan receipt:print ' . $orderId . ' --text');
            return 1;
        }

        $this->info("✅ Reçu {$order->order_number} imprimé avec succès !");
        return 0;
    }
}
