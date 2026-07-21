@extends('layouts.front')

@section('title', 'Commande confirmée — ' . $order->order_number)

@section('content')

@php
$waNumber = preg_replace('/\D/', '', \App\Models\Setting::get('social_whatsapp', ''));
$siteName = \App\Models\Setting::get('site_name', 'Chamse');
$isCodOrWa = in_array($order->payment_method, ['cod', 'whatsapp', null]);

$waLines = [
    "✅ *Commande #{$order->order_number}*",
    "📅 " . $order->created_at->format('d/m/Y à H:i'),
    "",
];
foreach ($order->items as $item) {
    $variant = $item->variant_name ? " ({$item->variant_name})" : "";
    $waLines[] = "▸ {$item->name}{$variant} × {$item->quantity}";
}
$waLines[] = "";
$waLines[] = "*Total : " . format_price($order->total) . "*";
$waLines[] = "";
$waLines[] = "📍 " . $order->shipping_address . ", " . $order->shipping_city;
$waLines[] = "📞 " . ($order->shipping_phone ?? '');
$waSuccessMsg = "Bonjour, je viens de passer la commande #{$order->order_number}. Pouvez-vous confirmer ?";
$waTrackingUrl = $waNumber ? "https://wa.me/{$waNumber}?text=" . rawurlencode($waSuccessMsg) : '#';
@endphp

{{-- Bande de statut --}}
<div class="bg-white border-b border-slate-200">
    <div class="container mx-auto px-4 py-6 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-green-50 rounded-2xl border border-green-200 mb-3">
            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Commande confirmée !</h1>
        <p class="text-slate-500 text-sm mt-1">Merci {{ $order->shipping_first_name }}, votre commande a bien été enregistrée.</p>
    </div>
</div>

<div class="bg-slate-50 min-h-screen py-8">
<div class="container mx-auto px-4 pb-12 max-w-3xl">

    {{-- Numéro de commande --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-xs text-slate-500 uppercase tracking-wider font-medium mb-0.5">Numéro de commande</p>
            <p class="text-xl font-bold text-slate-900 font-mono tracking-wide">{{ $order->order_number }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <div class="flex flex-col items-start sm:items-end gap-2">
            @if($order->payment_status === 'paid')
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 border border-green-200 rounded-full text-xs font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Paiement confirmé
            </span>
            @elseif($order->payment_method === 'cod')
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Paiement à la livraison
            </span>
            @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-600 border border-slate-200 rounded-full text-xs font-semibold">
                En attente
            </span>
            @endif
            <p class="text-sm font-bold text-slate-900">{{ format_price($order->total) }}</p>
        </div>
    </div>

    {{-- Bouton WhatsApp (si COD ou WhatsApp) --}}
    @if($isCodOrWa && $waNumber)
    <div class="bg-white rounded-xl border border-[#25D366] p-5 mb-5">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex-1">
                <p class="font-semibold text-slate-900 text-sm mb-1">Finaliser sur WhatsApp</p>
                <p class="text-xs text-slate-500">Notre équipe vous confirmera la livraison et les détails de votre commande.</p>
            </div>
            <a href="{{ $waTrackingUrl }}" target="_blank" rel="noopener noreferrer"
               class="flex-shrink-0 inline-flex items-center gap-2 px-5 py-3 bg-[#25D366] hover:bg-[#1da851] text-white font-bold rounded-xl transition-colors text-sm">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Contacter sur WhatsApp
            </a>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">

        {{-- Articles commandés --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Articles ({{ $order->items->count() }})
            </h3>
            <div class="space-y-3">
                @foreach($order->items as $item)
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-slate-100 rounded-lg overflow-hidden flex-shrink-0">
                        @if($item->product?->images?->where('is_primary', true)->first())
                        <img src="{{ asset('storage/' . $item->product->images->where('is_primary', true)->first()->path) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900 truncate">{{ $item->name }}</p>
                        @if($item->variant_name)
                        <p class="text-xs text-slate-500">{{ $item->variant_name }}</p>
                        @endif
                        <p class="text-xs text-slate-500">× {{ $item->quantity }}</p>
                    </div>
                    <p class="text-sm font-semibold text-slate-900 flex-shrink-0">{{ format_price($item->total) }}</p>
                </div>
                @endforeach
            </div>
            <div class="border-t border-slate-100 mt-4 pt-3 space-y-1.5 text-xs text-slate-600">
                <div class="flex justify-between">
                    <span>Sous-total</span>
                    <span>{{ format_price($order->subtotal) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-green-600 font-medium">
                    <span>Réduction</span>
                    <span>-{{ format_price($order->discount_amount) }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span>Livraison</span>
                    <span>{{ $order->shipping_amount > 0 ? format_price($order->shipping_amount) : 'Gratuite' }}</span>
                </div>
                <div class="flex justify-between font-bold text-sm text-slate-900 border-t border-slate-100 pt-2 mt-1">
                    <span>Total</span>
                    <span>{{ format_price($order->total) }}</span>
                </div>
            </div>
        </div>

        {{-- Adresse de livraison --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Adresse de livraison
            </h3>
            <div class="bg-slate-50 rounded-lg p-4 text-sm text-slate-700 space-y-0.5 leading-relaxed">
                <p class="font-semibold text-slate-900">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</p>
                <p>{{ $order->shipping_address }}</p>
                @if($order->shipping_address_2)<p>{{ $order->shipping_address_2 }}</p>@endif
                <p>{{ $order->shipping_city }}@if($order->shipping_postal_code) {{ $order->shipping_postal_code }}@endif</p>
                <p class="text-slate-500">{{ $order->shipping_country }}</p>
                @if($order->shipping_phone)
                <p class="text-slate-500 flex items-center gap-1 mt-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    {{ $order->shipping_phone }}
                </p>
                @endif
            </div>
            @if($order->billing_email)
            <div class="flex items-center gap-2 mt-3 text-xs text-slate-500">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Confirmation envoyée à <strong class="text-slate-700 ml-0.5">{{ $order->billing_email }}</strong>
            </div>
            @endif
            @if($order->notes)
            <div class="flex items-start gap-2 mt-3 text-xs text-slate-500">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                Note : {{ $order->notes }}
            </div>
            @endif
        </div>

    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('order-tracking.index') }}"
           class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
            Suivre ma commande
        </a>
        <a href="{{ route('shop.index') }}"
           class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-medium rounded-xl transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            Continuer mes achats
        </a>
        @auth
        <a href="{{ route('account.orders') }}"
           class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-medium rounded-xl transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Mes commandes
        </a>
        @endauth
    </div>

    <p class="text-center text-xs text-slate-400 mt-6">
        Besoin d'aide ?
        @if($waNumber)
        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline font-medium">Contactez-nous sur WhatsApp</a>
        @endif
    </p>

</div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var key = 'pixel_purchase_{{ $order->id }}';
    if (sessionStorage.getItem(key)) return;
    sessionStorage.setItem(key, '1');
    var v = {{ $order->total }}, id = {{ Js::from($order->order_number) }};
    if (window.trackPixel) window.trackPixel.purchase(id, v);
    if (window.trackGA4) window.trackGA4.purchase(id, v);
    if (window.ttq) window.ttq.track('PlaceAnOrder', { order_id: id, value: v, currency: 'XOF' });
});
</script>
@endpush
@endsection
