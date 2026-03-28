<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'customer_id', 'order_id', 'phone', 'direction', 'type',
        'message', 'status', 'sent_at', 'delivered_at', 'read_at', 'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function sendOrderConfirmation(Order $order): self
    {
        $waNumber = Setting::get('social_whatsapp', '');
        $phone = $order->billing_phone ?? $order->shipping_phone ?? '';

        $items = $order->items->map(fn($i) => "• {$i->name} x{$i->quantity} — " . format_price($i->total))->join("\n");

        $message = "🛍️ *Confirmation Commande #{$order->order_number}*\n\n"
            . "Bonjour {$order->billing_first_name},\n\n"
            . "Merci pour votre commande !\n\n"
            . "*Articles :*\n{$items}\n\n"
            . "*Total :* " . format_price($order->total) . "\n"
            . "*Paiement :* {$order->payment_method_label}\n\n"
            . "Nous vous tiendrons informe de l'avancement. 🚚";

        return static::create([
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'phone' => $phone,
            'direction' => 'outgoing',
            'type' => 'order_confirmation',
            'message' => $message,
            'status' => 'pending',
            'metadata' => ['wa_number' => $waNumber],
        ]);
    }

    public static function sendShippingNotification(Order $order): self
    {
        $phone = $order->billing_phone ?? $order->shipping_phone ?? '';

        $message = "📦 *Commande #{$order->order_number} expediee !*\n\n"
            . "Bonjour {$order->billing_first_name},\n\n"
            . "Votre commande a ete expediee.\n";

        if ($order->tracking_number) {
            $message .= "*Suivi :* {$order->tracking_number}\n";
        }

        $message .= "\nVous recevrez votre colis bientot ! 🎉";

        return static::create([
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'phone' => $phone,
            'direction' => 'outgoing',
            'type' => 'shipping',
            'message' => $message,
            'status' => 'pending',
        ]);
    }

    public function getWhatsAppUrl(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        return "https://wa.me/{$phone}?text=" . urlencode($this->message);
    }
}
