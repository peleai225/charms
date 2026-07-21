<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;

/**
 * Service d'impression thermique POS (80mm/58mm)
 * Compatible ESC/POS pour imprimantes USB/Network/Bluetooth
 */
class ThermalPrinterService
{
    /**
     * Générer le reçu en format ESC/POS brut
     * Retourne un tableau d'instructions pour impression directe
     */
    public function generateReceipt(Order $order, array $options = []): array
    {
        $siteName = Setting::get('site_name', config('app.name'));
        $siteAddress = Setting::get('contact_address', '');
        $sitePhone = Setting::get('contact_phone', '');
        $currency = Setting::get('currency_symbol', 'F CFA');

        $width = $options['width'] ?? 48; // 48 caractères pour 80mm, 32 pour 58mm

        $receipt = [];

        // En-tête centré
        $receipt[] = ['cmd' => 'align', 'value' => 'center'];
        $receipt[] = ['cmd' => 'text', 'value' => strtoupper($siteName), 'bold' => true, 'size' => 'large'];
        $receipt[] = ['cmd' => 'feed', 'lines' => 1];

        if ($siteAddress) {
            $receipt[] = ['cmd' => 'text', 'value' => $siteAddress, 'size' => 'small'];
        }
        if ($sitePhone) {
            $receipt[] = ['cmd' => 'text', 'value' => "Tel: $sitePhone", 'size' => 'small'];
        }

        $receipt[] = ['cmd' => 'feed', 'lines' => 1];
        $receipt[] = ['cmd' => 'text', 'value' => str_repeat('-', $width)];
        $receipt[] = ['cmd' => 'feed', 'lines' => 1];

        // Infos commande
        $receipt[] = ['cmd' => 'text', 'value' => 'RECU DE VENTE', 'bold' => true];
        $receipt[] = ['cmd' => 'text', 'value' => "No: {$order->order_number}"];
        $receipt[] = ['cmd' => 'text', 'value' => $order->created_at->format('d/m/Y H:i')];

        $receipt[] = ['cmd' => 'feed', 'lines' => 1];
        $receipt[] = ['cmd' => 'text', 'value' => str_repeat('-', $width)];
        $receipt[] = ['cmd' => 'feed', 'lines' => 1];

        // Articles
        $receipt[] = ['cmd' => 'align', 'value' => 'left'];

        foreach ($order->items as $item) {
            $qty = str_pad($item->quantity . 'x', 4, ' ', STR_PAD_RIGHT);
            $price = str_pad(number_format($item->total, 0, '', ' ') . ' ' . $currency, 12, ' ', STR_PAD_LEFT);
            $nameMaxLen = $width - 4 - 12 - 1;
            $name = mb_substr($item->name, 0, $nameMaxLen);

            $line = $qty . ' ' . str_pad($name, $nameMaxLen) . ' ' . $price;
            $receipt[] = ['cmd' => 'text', 'value' => $line];

            if ($item->variant_name) {
                $receipt[] = ['cmd' => 'text', 'value' => '     ' . $item->variant_name, 'size' => 'small'];
            }
        }

        $receipt[] = ['cmd' => 'feed', 'lines' => 1];
        $receipt[] = ['cmd' => 'text', 'value' => str_repeat('-', $width)];
        $receipt[] = ['cmd' => 'feed', 'lines' => 1];

        // Total
        $receipt[] = ['cmd' => 'align', 'value' => 'center'];
        $totalLine = 'TOTAL: ' . number_format($order->total, 0, '', ' ') . ' ' . $currency;
        $receipt[] = ['cmd' => 'text', 'value' => $totalLine, 'bold' => true, 'size' => 'large'];

        $receipt[] = ['cmd' => 'feed', 'lines' => 1];
        $receipt[] = ['cmd' => 'text', 'value' => str_repeat('-', $width)];
        $receipt[] = ['cmd' => 'feed', 'lines' => 1];

        // Paiement
        $receipt[] = ['cmd' => 'align', 'value' => 'left'];
        $paymentMethod = match($order->payment_method) {
            'cash' => 'Especes',
            'card' => 'Carte',
            'mobile_money' => 'Mobile Money',
            default => ucfirst($order->payment_method ?? 'N/A')
        };
        $receipt[] = ['cmd' => 'text', 'value' => "Paiement: $paymentMethod"];

        if (isset($options['change']) && $options['change'] > 0) {
            $receipt[] = ['cmd' => 'text', 'value' => 'Recu: ' . number_format($options['amount_received'], 0, '', ' ') . ' ' . $currency];
            $receipt[] = ['cmd' => 'text', 'value' => 'Monnaie: ' . number_format($options['change'], 0, '', ' ') . ' ' . $currency];
        }

        // Footer
        $receipt[] = ['cmd' => 'feed', 'lines' => 1];
        $receipt[] = ['cmd' => 'text', 'value' => str_repeat('-', $width)];
        $receipt[] = ['cmd' => 'feed', 'lines' => 1];
        $receipt[] = ['cmd' => 'align', 'value' => 'center'];
        $receipt[] = ['cmd' => 'text', 'value' => 'Merci pour votre achat !', 'size' => 'small'];
        $receipt[] = ['cmd' => 'text', 'value' => $siteName, 'size' => 'small'];

        // QR Code si URL commande disponible
        if (isset($options['order_url'])) {
            $receipt[] = ['cmd' => 'feed', 'lines' => 1];
            $receipt[] = ['cmd' => 'qrcode', 'value' => $options['order_url']];
        }

        // Cut paper
        $receipt[] = ['cmd' => 'feed', 'lines' => 3];
        $receipt[] = ['cmd' => 'cut'];

        return $receipt;
    }

    /**
     * Convertir en texte brut pour copier-coller ou debug
     */
    public function toPlainText(array $receipt): string
    {
        $text = '';
        foreach ($receipt as $instruction) {
            if ($instruction['cmd'] === 'text') {
                $text .= $instruction['value'] . "\n";
            } elseif ($instruction['cmd'] === 'feed') {
                $text .= str_repeat("\n", $instruction['lines'] ?? 1);
            }
        }
        return $text;
    }

    /**
     * Convertir en commandes ESC/POS binaires (nécessite mike42/escpos-php)
     */
    public function toEscPosCommands(array $receipt): string
    {
        // TODO: Implémenter quand mike42/escpos-php sera installé
        // Pour l'instant retourne du texte brut
        return $this->toPlainText($receipt);
    }
}
