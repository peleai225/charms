<?php

namespace App\Livewire\Cart;

use App\Models\Cart;
use App\Models\Setting;
use Livewire\Component;
use Livewire\Attributes\On;

class Drawer extends Component
{
    public bool $open = false;
    public string $locationAddress = '';
    public float  $locationLat     = 0.0;
    public float  $locationLng     = 0.0;

    #[On('open-cart-drawer')]
    public function openDrawer(): void
    {
        $this->open = true;
    }

    public function setLocation(string $address, float $lat = 0.0, float $lng = 0.0): void
    {
        $this->locationAddress = $address;
        $this->locationLat     = $lat;
        $this->locationLng     = $lng;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function removeItem(int $itemId): void
    {
        $cart = $this->getCart();
        $cart->removeItem($itemId);
        $this->dispatch('cart-count-updated', count: $cart->fresh()->items_count);
    }

    public function updateQty(int $itemId, int $qty): void
    {
        if ($qty < 1) {
            $this->removeItem($itemId);
            return;
        }
        $cart = $this->getCart();
        $cart->updateItemQuantity($itemId, $qty);
        $this->dispatch('cart-count-updated', count: $cart->fresh()->items_count);
    }

    private function getCart(): Cart
    {
        return Cart::getOrCreate(session()->getId(), auth()->user()?->customer);
    }

    private function buildWhatsappUrl(Cart $cart): string
    {
        $phone = preg_replace('/[^0-9]/', '', Setting::get('social_whatsapp', Setting::get('contact_phone', '')));
        $siteName = Setting::get('site_name', config('app.name'));

        $lines = ["🛒 *Nouvelle commande - {$siteName}*", ''];

        foreach ($cart->items as $item) {
            $name = $item->product->name;
            if ($item->variant) $name .= " ({$item->variant->label})";
            $lines[] = "▸ {$name}";
            $lines[] = "  Qté: {$item->quantity} × " . format_price($item->unit_price);
        }

        $lines[] = '';
        if ($cart->discount_amount > 0 && $cart->coupon) {
            $lines[] = "Sous-total : " . format_price($cart->subtotal);
            $lines[] = "Réduction ({$cart->coupon->code}) : -" . format_price($cart->discount_amount);
        }
        $lines[] = "*Total : " . format_price($cart->total) . "*";
        $lines[] = '';
        if (!empty($this->locationAddress)) {
            $lines[] = '📍 Adresse de livraison : ' . $this->locationAddress;
            if ($this->locationLat && $this->locationLng) {
                $lines[] = '🗺 Carte : https://www.google.com/maps?q=' . $this->locationLat . ',' . $this->locationLng;
            }
        } else {
            $lines[] = '📍 Adresse de livraison : (à préciser)';
        }
        $lines[] = '📞 Mon numéro : (à préciser)';

        $message = implode("\n", $lines);
        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }

    public function render()
    {
        $cart = $this->getCart()->load([
            'items.product.images',
            'items.variant',
            'coupon',
        ]);

        $whatsappUrl = $this->buildWhatsappUrl($cart);
        $hasWhatsapp = !empty(Setting::get('social_whatsapp', Setting::get('contact_phone', '')));

        return view('livewire.cart.drawer', compact('cart', 'whatsappUrl', 'hasWhatsapp'));
    }
}
