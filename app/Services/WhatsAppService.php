<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use App\Models\WhatsAppMessage;

class WhatsAppService
{
    public static function getWhatsAppNumber(): string
    {
        return preg_replace('/[^0-9]/', '', Setting::get('social_whatsapp', ''));
    }

    public static function buildWhatsAppUrl(string $phone, string $message): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }

    public static function buildOrderWhatsAppUrl(Order $order): string
    {
        $waNumber = self::getWhatsAppNumber();
        $items = $order->items->map(fn($i) => "- {$i->name} x{$i->quantity}")->join("\n");

        $message = "Bonjour, je viens de passer la commande #{$order->order_number}\n\n"
            . "Articles :\n{$items}\n\n"
            . "Total : " . format_price($order->total) . "\n\n"
            . "Merci !";

        return self::buildWhatsAppUrl($waNumber, $message);
    }

    public static function buildProductWhatsAppUrl($product): string
    {
        $waNumber = self::getWhatsAppNumber();
        $price = format_price($product->sale_price ?? $product->price);
        $url = route('shop.product', $product->slug);

        $message = "Bonjour, je suis interesse par ce produit :\n\n"
            . "*{$product->name}*\n"
            . "Prix : {$price}\n"
            . "Lien : {$url}\n\n"
            . "Est-il disponible ?";

        return self::buildWhatsAppUrl($waNumber, $message);
    }

    public static function sendAbandonedCartReminder(Customer $customer, $cart): void
    {
        $phone = $customer->phone;
        if (!$phone) return;

        $items = collect($cart->items ?? [])->map(fn($i) => "• {$i['name']}")->join("\n");

        $message = "👋 Bonjour {$customer->first_name} !\n\n"
            . "Vous avez laisse des articles dans votre panier :\n{$items}\n\n"
            . "Finalisez votre commande maintenant et profitez de nos offres ! 🎉\n\n"
            . "👉 " . route('cart.index');

        WhatsAppMessage::create([
            'customer_id' => $customer->id,
            'phone' => $phone,
            'direction' => 'outgoing',
            'type' => 'abandoned_cart',
            'message' => $message,
            'status' => 'pending',
        ]);
    }

    public static function sendPostDeliveryMessage(Order $order): void
    {
        $phone = $order->billing_phone ?? $order->shipping_phone;
        if (!$phone) return;

        $message = "🎉 Bonjour {$order->billing_first_name} !\n\n"
            . "Votre commande #{$order->order_number} a ete livree.\n\n"
            . "Nous esperons que tout vous convient !\n"
            . "N'hesitez pas a laisser un avis sur nos produits. ⭐\n\n"
            . "A bientot sur " . Setting::get('site_name', config('app.name')) . " !";

        WhatsAppMessage::create([
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'phone' => $phone,
            'direction' => 'outgoing',
            'type' => 'post_delivery',
            'message' => $message,
            'status' => 'pending',
        ]);
    }
}
