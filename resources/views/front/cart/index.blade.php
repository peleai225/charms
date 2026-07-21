@extends('layouts.front')

@section('title', 'Mon panier')

@section('content')

@php
$waNumber = preg_replace('/\D/', '', \App\Models\Setting::get('social_whatsapp', ''));
$siteName = \App\Models\Setting::get('site_name', 'Chamse');
if (!$cart->is_empty) {
    $waLines = ["🛒 *Commande - {$siteName}*", ""];
    foreach ($cart->items as $item) {
        $varLabel = $item->variant ? " ({$item->variant->label})" : "";
        $waLines[] = "▸ {$item->product->name}{$varLabel} × {$item->quantity} — " . format_price($item->unit_price);
    }
    $waLines[] = "";
    $waLines[] = "*Total : " . format_price($cart->total) . "*";
    $waLines[] = "";
    $waLines[] = "📍 Adresse : (à préciser)";
    $waLines[] = "📞 Téléphone : (à préciser)";
    $waUrl = $waNumber ? "https://wa.me/{$waNumber}?text=" . rawurlencode(implode("\n", $waLines)) : '#';
} else {
    $waUrl = '#';
}
@endphp

<div class="bg-slate-50 border-b border-slate-200 py-6">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-500 flex items-center gap-2 mb-1">
            <a href="{{ route('home') }}" class="hover:text-slate-800 transition-colors">Accueil</a>
            <span>/</span>
            <span class="text-slate-800 font-medium">Panier</span>
        </nav>
        <h1 class="text-2xl font-bold text-slate-900">Mon panier</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-8 pb-12">

    @if(session('error'))
    <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @if(!$cart->is_empty)
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        {{-- ===== Articles (60%) ===== --}}
        <div class="lg:col-span-3 space-y-3">
            <div class="flex items-center justify-between mb-1">
                <h2 class="font-semibold text-slate-900">
                    Mon panier ({{ $cart->items_count }} article{{ $cart->items_count > 1 ? 's' : '' }})
                </h2>
                <form method="POST" action="{{ route('cart.clear') }}"
                      x-data x-on:submit.prevent="if(confirm('Vider tout le panier ?')) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-slate-400 hover:text-red-500 transition-colors">
                        Vider le panier
                    </button>
                </form>
            </div>

            @foreach($cart->items as $item)
            <div class="bg-white rounded-xl border border-slate-200 p-4 flex gap-4"
                 x-data="cartItem({{ $item->id }}, {{ $item->quantity }}, {{ $item->unit_price }})"
                 :class="{ 'opacity-50 pointer-events-none': isUpdating || isRemoving }">

                <a href="{{ route('shop.product', $item->product->slug) }}" class="flex-shrink-0">
                    <div class="w-20 h-20 rounded-lg bg-slate-100 overflow-hidden border border-slate-100">
                        @if($item->variant?->image)
                            <img src="{{ asset('storage/' . $item->variant->image) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        @elseif($item->product->images->where('is_primary', true)->first())
                            <img src="{{ asset('storage/' . $item->product->images->where('is_primary', true)->first()->path) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                    </div>
                </a>

                <div class="flex-1 min-w-0">
                    <a href="{{ route('shop.product', $item->product->slug) }}"
                       class="font-medium text-slate-900 hover:text-blue-600 transition-colors line-clamp-2 text-sm leading-snug">
                        {{ $item->product->name }}
                    </a>
                    @if($item->variant)
                        <p class="text-xs text-slate-500 mt-0.5">{{ $item->variant->label ?? $item->variant->name }}</p>
                    @endif
                    <p class="text-xs text-slate-400 mt-1">{{ format_price($item->unit_price) }} / unité</p>

                    {{-- Mobile : qty + suppression --}}
                    <div class="flex items-center justify-between mt-3 lg:hidden">
                        <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden">
                            <button type="button" @click="decrementQuantity()" :disabled="quantity <= 1"
                                class="px-3 py-1.5 hover:bg-slate-50 disabled:opacity-40 transition-colors">
                                <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            </button>
                            <span x-text="quantity" class="px-3 text-sm font-medium text-slate-800 min-w-[2rem] text-center"></span>
                            <button type="button" @click="incrementQuantity()" :disabled="quantity >= 99"
                                class="px-3 py-1.5 hover:bg-slate-50 disabled:opacity-40 transition-colors">
                                <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <p class="font-semibold text-slate-900 text-sm" x-text="formatPrice(lineTotal)"></p>
                            <button type="button" @click="removeItem()"
                                class="text-slate-400 hover:text-red-500 transition-colors p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Desktop : qty + prix + suppression --}}
                <div class="hidden lg:flex items-center gap-4">
                    <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden">
                        <button type="button" @click="decrementQuantity()" :disabled="quantity <= 1"
                            class="px-3 py-2 hover:bg-slate-50 disabled:opacity-40 transition-colors" aria-label="Diminuer">
                            <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <input type="number" x-model.number="quantity" @change="debouncedUpdate()" min="1" max="99"
                            class="w-10 text-center border-0 text-sm focus:ring-0 text-slate-800 bg-transparent" aria-label="Quantité">
                        <button type="button" @click="incrementQuantity()" :disabled="quantity >= 99"
                            class="px-3 py-2 hover:bg-slate-50 disabled:opacity-40 transition-colors" aria-label="Augmenter">
                            <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                    <p class="font-semibold text-slate-900 text-sm min-w-[90px] text-right" x-text="formatPrice(lineTotal)"></p>
                    <button type="button" @click="removeItem()" aria-label="Supprimer"
                        class="text-slate-400 hover:text-red-500 transition-colors p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            @endforeach

            <div class="pt-2">
                <a href="{{ route('shop.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Continuer mes achats
                </a>
            </div>
        </div>

        {{-- ===== Récapitulatif (40%) ===== --}}
        <div class="lg:col-span-2">
            <div class="bg-slate-50 rounded-xl border border-slate-200 p-5 sticky top-24">
                <h2 class="text-base font-semibold text-slate-900 mb-4">Récapitulatif</h2>

                {{-- Code promo --}}
                <div class="mb-4" x-data="couponForm()">
                    @if($cart->coupon_code)
                    <div class="flex items-center justify-between px-3 py-2.5 bg-green-50 border border-green-200 rounded-lg">
                        <div>
                            <span class="text-sm font-semibold text-green-700">{{ $cart->coupon_code }}</span>
                            <span class="text-xs text-green-600 block">Code promo appliqué</span>
                        </div>
                        <form method="POST" action="{{ route('cart.coupon.remove') }}">
                            @csrf @method('DELETE')
                            <button type="submit" aria-label="Retirer le code promo">
                                <svg class="w-4 h-4 text-green-500 hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                    @else
                    <form @submit.prevent="applyCoupon($event)" class="flex gap-2">
                        @csrf
                        <input type="text" x-model="couponCode" name="coupon_code" placeholder="Code promo"
                            class="flex-1 px-3 py-2 border border-slate-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors uppercase">
                        <button type="submit" :disabled="isApplying"
                            class="px-3 py-2 bg-slate-700 hover:bg-slate-800 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap min-w-[90px] flex items-center justify-center">
                            <svg x-show="isApplying" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span x-show="!isApplying">Appliquer</span>
                        </button>
                    </form>
                    @endif
                </div>

                {{-- Totaux --}}
                <div class="space-y-2 text-sm border-t border-slate-200 pt-4">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Sous-total (<span id="cart-count">{{ $cart->items_count }}</span> art.)</span>
                        <span class="font-medium text-slate-900" id="cart-summary-subtotal">{{ format_price($cart->subtotal) }}</span>
                    </div>
                    @if($cart->discount_amount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Réduction</span>
                        <span>-{{ format_price($cart->discount_amount) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-slate-600">Livraison</span>
                        <span class="text-xs text-slate-500">Calculée à l'étape suivante</span>
                    </div>
                    <div class="flex justify-between items-baseline border-t border-slate-200 pt-2 mt-1">
                        <span class="font-bold text-slate-900">Total</span>
                        <span class="font-bold text-lg text-slate-900" id="cart-summary-total">{{ format_price($cart->total) }}</span>
                    </div>
                </div>

                {{-- CTA WhatsApp — PRINCIPAL --}}
                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                   class="mt-5 flex items-center justify-center gap-2 w-full py-4 rounded-xl bg-[#25D366] hover:bg-[#1da851] text-white font-bold text-sm transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Commander via WhatsApp
                </a>
                <p class="text-center text-xs text-slate-500 mt-1.5 mb-3">Recommandé · Réponse rapide en Côte d'Ivoire</p>

                {{-- CTA Paiement en ligne — SECONDAIRE --}}
                <a href="{{ route('checkout.index') }}"
                   class="flex items-center justify-center gap-2 w-full py-3 border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-medium rounded-xl transition-colors text-sm">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Paiement en ligne
                </a>

                {{-- Réassurance --}}
                <ul class="mt-4 space-y-1.5 text-xs text-slate-500">
                    <li class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Paiement 100% sécurisé
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Livraison partout en Côte d'Ivoire
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Retours gratuits sous 30 jours
                    </li>
                </ul>
            </div>
        </div>

    </div>

    @else
    {{-- Panier vide --}}
    <div class="text-center py-24">
        <div class="w-20 h-20 mx-auto mb-6 bg-slate-100 rounded-2xl flex items-center justify-center">
            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-900 mb-2">Votre panier est vide</h2>
        <p class="text-sm text-slate-500 mb-8 max-w-xs mx-auto">Découvrez nos produits et ajoutez-les à votre panier pour passer commande.</p>
        <a href="{{ route('shop.index') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            Découvrir la boutique
        </a>
    </div>
    @endif

</div>

@push('scripts')
<script>
function couponForm() {
    return {
        couponCode: '', isApplying: false,
        async applyCoupon(e) {
            e.preventDefault();
            if (!this.couponCode.trim() || this.isApplying) return;
            this.isApplying = true;
            try {
                const res = await fetch('{{ route('cart.coupon.apply') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ coupon_code: this.couponCode.trim() })
                });
                const data = await res.json();
                if (res.ok && data.success) { window.location.reload(); }
                else { alert(data.error || data.message || 'Code invalide.'); this.isApplying = false; }
            } catch { alert('Erreur réseau. Réessayez.'); this.isApplying = false; }
        }
    };
}

function cartItem(itemId, initialQty, unitPrice) {
    return {
        itemId, quantity: initialQty, unitPrice,
        isUpdating: false, isRemoving: false, timer: null,
        get lineTotal() { return this.quantity * this.unitPrice; },
        formatPrice(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' F CFA'; },
        incrementQuantity() { if (this.quantity < 99) { this.quantity++; this.debouncedUpdate(); } },
        decrementQuantity() { if (this.quantity > 1) { this.quantity--; this.debouncedUpdate(); } },
        debouncedUpdate() { clearTimeout(this.timer); this.timer = setTimeout(() => this.updateQuantity(), 500); },
        async updateQuantity() {
            if (this.isUpdating) return;
            this.isUpdating = true;
            try {
                const res = await fetch(`/api/cart/items/${this.itemId}`, {
                    method: 'PATCH', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ quantity: this.quantity })
                });
                if (res.ok) {
                    const data = await res.json();
                    if (Alpine.store && Alpine.store('cart')) { Alpine.store('cart').count = data.items_count; Alpine.store('cart').sync?.(); }
                    this.refreshSummary();
                }
            } catch(e) { console.error(e); } finally { this.isUpdating = false; }
        },
        async removeItem() {
            if (this.isRemoving) return;
            this.isRemoving = true;
            try {
                const res = await fetch(`/api/cart/items/${this.itemId}`, {
                    method: 'DELETE', credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (Alpine.store && Alpine.store('cart')) Alpine.store('cart').count = data.items_count;
                    this.$el.remove();
                    if (data.items_count === 0) window.location.reload();
                    else this.refreshSummary();
                }
            } catch(e) { console.error(e); this.isRemoving = false; }
        },
        async refreshSummary() {
            try {
                const res = await fetch('/api/cart');
                if (!res.ok) return;
                const data = await res.json();
                const sub = document.getElementById('cart-summary-subtotal');
                const tot = document.getElementById('cart-summary-total');
                if (sub) sub.textContent = this.formatPrice(data.subtotal);
                if (tot) tot.textContent = this.formatPrice(data.total);
            } catch(e) {}
        }
    };
}
</script>
@endpush
@endsection
