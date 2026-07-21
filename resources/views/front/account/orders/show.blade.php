@extends('layouts.front')

@section('title', 'Commande #' . $order->order_number)

@section('content')
@php
    $waNumber = \App\Models\Setting::get('whatsapp_number', '2250700000000');
    $waMsg = urlencode('Bonjour, je contacte pour la commande #' . $order->order_number);
    $waLink = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $waNumber) . '?text=' . $waMsg;

    $steps = [
        ['key' => 'pending',    'label' => 'En attente'],
        ['key' => 'processing', 'label' => 'Confirmée'],
        ['key' => 'shipped',    'label' => 'Expédiée'],
        ['key' => 'delivered',  'label' => 'Livrée'],
    ];
    $statusOrder = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3];
    $currentStep = $statusOrder[$order->status] ?? 0;
    $isCancelled = $order->status === 'cancelled';
@endphp

<div class="bg-slate-50 border-b border-slate-200 py-8">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-2 flex items-center gap-2 flex-wrap">
            <a href="{{ route('home') }}" class="hover:text-slate-700 transition-colors">Accueil</a>
            <span>/</span>
            <a href="{{ route('account.dashboard') }}" class="hover:text-slate-700 transition-colors">Mon compte</a>
            <span>/</span>
            <a href="{{ route('account.orders') }}" class="hover:text-slate-700 transition-colors">Commandes</a>
            <span>/</span>
            <span class="text-slate-700">#{{ $order->order_number }}</span>
        </nav>
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-bold text-slate-900">Commande #{{ $order->order_number }}</h1>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                @if($order->status === 'delivered') bg-green-100 text-green-700
                @elseif($order->status === 'cancelled') bg-red-100 text-red-700
                @elseif($order->status === 'shipped') bg-indigo-100 text-indigo-700
                @elseif($order->status === 'processing') bg-blue-100 text-blue-700
                @else bg-amber-100 text-amber-700 @endif">
                {{ $order->status_label }}
            </span>
        </div>
        <p class="text-sm text-slate-500 mt-1">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('front.account.partials.sidebar')

            <div class="flex-1 min-w-0 space-y-5">

                {{-- Timeline statut --}}
                @if(!$isCancelled)
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h2 class="text-sm font-semibold text-slate-700 mb-4">Suivi de commande</h2>
                    <div class="flex items-center">
                        @foreach($steps as $i => $step)
                        <div class="flex-1 flex flex-col items-center relative">
                            {{-- Ligne de connexion --}}
                            @if($i < count($steps) - 1)
                            <div class="absolute top-3.5 left-1/2 right-0 h-0.5
                                {{ $currentStep > $i ? 'bg-blue-600' : 'bg-slate-200' }}" style="width: calc(100% - 12px); transform: translateX(50%);">
                            </div>
                            @endif
                            {{-- Point --}}
                            <div class="w-7 h-7 rounded-full flex items-center justify-center z-10 flex-shrink-0
                                {{ $currentStep >= $i ? 'bg-blue-600' : 'bg-slate-200' }}">
                                @if($currentStep > $i)
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                                @elseif($currentStep === $i)
                                <div class="w-2.5 h-2.5 bg-white rounded-full"></div>
                                @endif
                            </div>
                            <p class="text-xs mt-1.5 text-center {{ $currentStep >= $i ? 'text-blue-600 font-semibold' : 'text-slate-400' }}">
                                {{ $step['label'] }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="grid lg:grid-cols-3 gap-5">

                    {{-- Articles (col 2/3) --}}
                    <div class="lg:col-span-2 space-y-5">

                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-100">
                                <h2 class="font-semibold text-slate-900">Articles commandés</h2>
                            </div>
                            <div class="divide-y divide-slate-100">
                                @foreach($order->items as $item)
                                <div class="px-5 py-4 flex gap-4">
                                    <div class="w-16 h-16 bg-slate-100 rounded-xl overflow-hidden flex-shrink-0">
                                        @if($item->product && $item->product->primary_image_url)
                                            <img src="{{ $item->product->primary_image_url }}"
                                                 alt="{{ $item->product->name }}"
                                                 class="w-full h-full object-cover" loading="lazy">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-slate-900 text-sm">{{ $item->product?->name ?? 'Produit supprimé' }}</p>
                                        @if($item->productVariant)
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            @foreach($item->productVariant->attributeValues as $av)
                                                {{ $av->attribute->name }}: {{ $av->value }}@if(!$loop->last), @endif
                                            @endforeach
                                        </p>
                                        @endif
                                        <p class="text-xs text-slate-400 mt-0.5">Qté : {{ $item->quantity }} &times; {{ number_format($item->unit_price, 0, ',', ' ') }} F</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="font-semibold text-slate-900 text-sm">{{ number_format($item->total, 0, ',', ' ') }} F</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 space-y-1.5">
                                <div class="flex justify-between text-sm text-slate-600">
                                    <span>Sous-total</span>
                                    <span>{{ number_format($order->subtotal, 0, ',', ' ') }} F</span>
                                </div>
                                @if($order->discount_amount > 0)
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>Réduction</span>
                                    <span>- {{ number_format($order->discount_amount, 0, ',', ' ') }} F</span>
                                </div>
                                @endif
                                <div class="flex justify-between text-sm text-slate-600">
                                    <span>Livraison</span>
                                    <span>{{ $order->shipping_amount > 0 ? number_format($order->shipping_amount, 0, ',', ' ') . ' F' : 'Gratuit' }}</span>
                                </div>
                                <div class="flex justify-between font-bold text-slate-900 pt-1.5 border-t border-slate-200 mt-1.5">
                                    <span>Total</span>
                                    <span>{{ number_format($order->total, 0, ',', ' ') }} F CFA</span>
                                </div>
                            </div>
                        </div>

                        {{-- Adresse livraison --}}
                        <div class="bg-white rounded-2xl border border-slate-200 p-5">
                            <h3 class="font-semibold text-slate-900 mb-3">Adresse de livraison</h3>
                            <div class="text-sm text-slate-600 space-y-0.5">
                                <p class="font-medium text-slate-900">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</p>
                                <p>{{ $order->shipping_address }}</p>
                                @if($order->shipping_address2)
                                    <p>{{ $order->shipping_address2 }}</p>
                                @endif
                                <p>{{ $order->shipping_postal_code }} {{ $order->shipping_city }}</p>
                                <p>{{ $order->shipping_country }}</p>
                                @if($order->shipping_phone)
                                    <p class="mt-1">{{ $order->shipping_phone }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Résumé paiement (col 1/3) --}}
                    <div class="space-y-5">
                        <div class="bg-white rounded-2xl border border-slate-200 p-5">
                            <h3 class="font-semibold text-slate-900 mb-3">Paiement</h3>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <p class="text-slate-500 text-xs">Méthode</p>
                                    <p class="font-medium text-slate-900">{{ $order->payment_method_label ?? 'Non définie' }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500 text-xs">Statut</p>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($order->payment_status === 'paid') bg-green-100 text-green-700
                                        @elseif($order->payment_status === 'failed') bg-red-100 text-red-700
                                        @else bg-amber-100 text-amber-700 @endif">
                                        @if($order->payment_status === 'paid') Payé
                                        @elseif($order->payment_status === 'failed') Échoué
                                        @else En attente @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($order->tracking_number)
                        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
                            <h3 class="font-semibold text-blue-900 mb-2 text-sm">Numéro de suivi</h3>
                            <p class="font-mono text-blue-800 font-bold">{{ $order->tracking_number }}</p>
                            @if($order->shipping_carrier)
                                <p class="text-xs text-blue-600 mt-1">{{ $order->shipping_carrier }}</p>
                            @endif
                        </div>
                        @endif

                        <a href="{{ $waLink }}" target="_blank" rel="noopener"
                           class="flex items-center gap-2.5 px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-2xl text-sm font-medium transition-colors w-full justify-center">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            Besoin d'aide ?
                        </a>

                        <a href="{{ route('account.orders') }}"
                           class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors w-full justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Retour aux commandes
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
