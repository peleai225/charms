@extends('layouts.front')

@section('title', 'Commande confirmée')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-emerald-50/60 via-white to-white">

    {{-- Bande de confirmation --}}
    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl mb-3 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black">Commande confirmée !</h1>
            <p class="text-emerald-100 mt-1 text-sm">Merci pour votre confiance, {{ $order->shipping_first_name }}.</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-10 max-w-3xl">

        {{-- Numéro + statut paiement --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold mb-1">Numéro de commande</p>
                <p class="text-xl font-black text-slate-900 font-mono">{{ $order->order_number }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div class="flex flex-col items-start sm:items-end gap-2">
                @if($order->payment_status === 'paid')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Paiement confirmé
                </span>
                @elseif($order->payment_method === 'cod')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    Paiement à la livraison
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Paiement en attente
                </span>
                @endif
                <p class="text-xs text-slate-500">Total : <span class="font-bold text-slate-800">{{ number_format($order->total, 0, ',', ' ') }} F CFA</span></p>
            </div>
        </div>

        {{-- Timeline de suivi --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
            <h2 class="text-base font-bold text-slate-900 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                Suivi de votre commande
            </h2>

            @php
                $statuses = [
                    'pending'    => ['label' => 'Commande reçue',       'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',     'desc' => 'Votre commande a été enregistrée'],
                    'confirmed'  => ['label' => 'Confirmée',            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                            'desc' => 'Votre commande est confirmée'],
                    'processing' => ['label' => 'En préparation',       'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',                                                      'desc' => 'Votre colis est en cours de préparation'],
                    'shipped'    => ['label' => 'Expédiée',             'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',                                                      'desc' => 'Votre colis est en chemin'],
                    'delivered'  => ['label' => 'Livrée',               'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'desc' => 'Votre colis a été livré'],
                ];
                $statusOrder = array_keys($statuses);
                $currentIdx = array_search($order->status, $statusOrder) ?? 0;
            @endphp

            <div class="relative">
                {{-- Ligne de progression --}}
                <div class="absolute top-5 left-5 right-5 h-0.5 bg-slate-100 hidden sm:block"></div>
                <div class="absolute top-5 left-5 h-0.5 bg-emerald-500 hidden sm:block transition-all duration-700"
                     style="width: calc({{ $currentIdx }} / {{ count($statusOrder) - 1 }} * (100% - 40px))"></div>

                <div class="relative grid grid-cols-1 sm:grid-cols-5 gap-4 sm:gap-0">
                    @foreach($statuses as $statusKey => $step)
                    @php
                        $stepIdx = array_search($statusKey, $statusOrder);
                        $isDone = $stepIdx <= $currentIdx;
                        $isCurrent = $stepIdx === $currentIdx;
                    @endphp
                    <div class="flex sm:flex-col items-center sm:items-center gap-3 sm:gap-2">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 relative z-10 transition-all duration-500 border-2
                            {{ $isCurrent ? 'bg-emerald-500 border-emerald-500 shadow-lg shadow-emerald-500/30 scale-110' : ($isDone ? 'bg-emerald-500 border-emerald-500' : 'bg-white border-slate-200') }}">
                            @if($isDone)
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($isCurrent)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                @endif
                            </svg>
                            @else
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
                            </svg>
                            @endif
                        </div>
                        <div class="sm:text-center">
                            <p class="text-xs font-semibold {{ $isDone ? 'text-slate-900' : 'text-slate-400' }}">{{ $step['label'] }}</p>
                            @if($isCurrent)
                            <p class="text-[10px] text-emerald-600 font-medium hidden sm:block mt-0.5">{{ $step['desc'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($order->tracking_number)
            <div class="mt-6 p-4 bg-slate-50 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                <div>
                    <p class="text-xs text-slate-500">Numéro de suivi transporteur</p>
                    <p class="font-mono font-bold text-slate-900">{{ $order->tracking_number }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- 2 colonnes : articles + adresse --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            {{-- Articles commandés --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Articles ({{ $order->items->count() }})
                </h3>
                <div class="space-y-3">
                    @foreach($order->items as $item)
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-slate-100 rounded-xl overflow-hidden flex-shrink-0">
                            @if($item->product?->images?->where('is_primary', true)->first())
                            <img src="{{ asset('storage/' . $item->product->images->where('is_primary', true)->first()->path) }}" alt="" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $item->name ?? $item->product_name }}</p>
                            @if($item->variant_name)
                            <p class="text-xs text-slate-500">{{ $item->variant_name }}</p>
                            @endif
                            <p class="text-xs text-slate-500">× {{ $item->quantity }}</p>
                        </div>
                        <p class="text-sm font-bold text-slate-900">{{ number_format($item->total, 0, ',', ' ') }} F</p>
                    </div>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 mt-4 pt-3 space-y-1.5 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Sous-total</span>
                        <span>{{ number_format($order->subtotal, 0, ',', ' ') }} F</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-600 font-medium">
                        <span>Réduction</span>
                        <span>-{{ number_format($order->discount_amount, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span>Livraison</span>
                        <span>{{ $order->shipping_amount > 0 ? number_format($order->shipping_amount, 0, ',', ' ') . ' F' : 'Gratuite' }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-sm text-slate-900 pt-1.5 border-t border-slate-100">
                        <span>Total</span>
                        <span class="text-primary-600">{{ number_format($order->total, 0, ',', ' ') }} F CFA</span>
                    </div>
                </div>
            </div>

            {{-- Livraison --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Adresse de livraison
                </h3>
                <div class="bg-slate-50 rounded-xl p-4 text-sm text-slate-700 space-y-1 leading-relaxed">
                    <p class="font-semibold text-slate-900">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</p>
                    <p>{{ $order->shipping_address }}</p>
                    @if($order->shipping_address_2)<p>{{ $order->shipping_address_2 }}</p>@endif
                    <p>{{ $order->shipping_postal_code }} {{ $order->shipping_city }}</p>
                    <p class="text-slate-500">{{ $order->shipping_country }}</p>
                    @if($order->shipping_phone)
                    <p class="text-slate-500 flex items-center gap-1 mt-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $order->shipping_phone }}
                    </p>
                    @endif
                </div>

                {{-- Infos supplémentaires --}}
                <div class="mt-4 space-y-2 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Confirmation envoyée à <strong class="text-slate-700">{{ $order->billing_email }}</strong></span>
                    </div>
                    @if($order->notes)
                    <div class="flex items-start gap-2 mt-2">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        <span>Note : {{ $order->notes }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('shop.index') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/25 hover:-translate-y-0.5 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Continuer mes achats
            </a>
            <a href="{{ route('order-tracking.index') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-xl border border-slate-200 hover:border-slate-300 transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                Suivre ma commande
            </a>
            @auth
            <a href="{{ route('account.orders') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Mes commandes
            </a>
            @endauth
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">Besoin d'aide ? <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('app.whatsapp_number', '2250506805382')) }}" class="text-primary-600 hover:underline font-medium">Contactez-nous sur WhatsApp</a></p>
    </div>
</div>

@push('scripts')
<script>
// Pixels — Purchase (sessionStorage évite le double-fire en cas de refresh)
document.addEventListener('DOMContentLoaded', function() {
    var _key = 'pixel_purchase_{{ $order->id }}';
    if (sessionStorage.getItem(_key)) return;
    sessionStorage.setItem(_key, '1');
    var _v = {{ $order->total }}, _id = {{ Js::from($order->order_number) }};
    if (window.trackPixel) window.trackPixel.purchase(_id, _v);
    if (window.trackGA4) window.trackGA4.purchase(_id, _v);
    if (window.ttq) window.ttq.track('PlaceAnOrder', { order_id: _id, value: _v, currency: 'XOF' });
});
</script>
@endpush
@endsection
