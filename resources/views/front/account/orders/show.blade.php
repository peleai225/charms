@extends('layouts.front')

@section('title', 'Commande #' . $order->order_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-500 mb-8">
        <a href="{{ route('home') }}" class="hover:text-primary-600">Accueil</a>
        <span class="mx-2">/</span>
        <a href="{{ route('account.dashboard') }}" class="hover:text-primary-600">Mon compte</a>
        <span class="mx-2">/</span>
        <a href="{{ route('account.orders') }}" class="hover:text-primary-600">Mes commandes</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900">#{{ $order->order_number }}</span>
    </nav>

    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            @include('front.account.partials.sidebar')

            <!-- Main Content -->
            <div class="flex-1">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-slate-900">Commande #{{ $order->order_number }}</h1>
                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium
                        @if($order->status === 'delivered') bg-green-100 text-green-800
                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                        @elseif($order->status === 'shipped') bg-blue-100 text-blue-800
                        @else bg-amber-100 text-amber-800 @endif">
                        {{ $order->status_label }}
                    </span>
                </div>

                <!-- Informations commande -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Date de commande</p>
                            <p class="font-medium text-slate-900">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Méthode de paiement</p>
                            <p class="font-medium text-slate-900">{{ $order->payment_method_label ?? 'Non définie' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Statut paiement</p>
                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                                @if($order->payment_status === 'paid') bg-green-100 text-green-800
                                @elseif($order->payment_status === 'failed') bg-red-100 text-red-800
                                @else bg-amber-100 text-amber-800 @endif">
                                @if($order->payment_status === 'paid') Payé
                                @elseif($order->payment_status === 'failed') Échoué
                                @else En attente @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Suivi de livraison -->
                @if($order->tracking_number)
                <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6 mb-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-100 rounded-xl">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-blue-600 mb-1">Numéro de suivi</p>
                            <p class="font-semibold text-blue-900">{{ $order->tracking_number }}</p>
                            @if($order->shipping_carrier)
                                <p class="text-sm text-blue-700">Transporteur : {{ $order->shipping_carrier }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Produits -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6">
                    <div class="p-6 border-b border-slate-100">
                        <h2 class="text-lg font-semibold text-slate-900">Articles commandés</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($order->items as $item)
                        <div class="p-6 flex gap-4">
                            <div class="w-20 h-20 bg-slate-100 rounded-lg overflow-hidden flex-shrink-0">
                                @if($item->product && $item->product->primary_image_url)
                                    <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-slate-900">{{ $item->product?->name ?? 'Produit supprimé' }}</h3>
                                @if($item->productVariant)
                                    <p class="text-sm text-slate-500">
                                        @foreach($item->productVariant->attributeValues as $av)
                                            {{ $av->attribute->name }}: {{ $av->value }}@if(!$loop->last), @endif
                                        @endforeach
                                    </p>
                                @endif
                                <p class="text-sm text-slate-500 mt-1">Quantité : {{ $item->quantity }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-slate-900">{{ number_format($item->total, 0, ',', ' ') }} F</p>
                                <p class="text-sm text-slate-500">{{ number_format($item->unit_price, 0, ',', ' ') }} F / unité</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Totaux -->
                    <div class="p-6 bg-slate-50 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Sous-total</span>
                            <span class="text-slate-900">{{ number_format($order->subtotal, 0, ',', ' ') }} F</span>
                        </div>
                        @if($order->discount_amount > 0)
                        <div class="flex justify-between text-sm text-green-600">
                            <span>Réduction</span>
                            <span>- {{ number_format($order->discount_amount, 0, ',', ' ') }} F</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Livraison</span>
                            <span class="text-slate-900">
                                @if($order->shipping_amount > 0)
                                    {{ number_format($order->shipping_amount, 0, ',', ' ') }} F
                                @else
                                    Gratuit
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-200">
                            <span class="text-slate-900">Total</span>
                            <span class="text-slate-900">{{ number_format($order->total, 0, ',', ' ') }} F</span>
                        </div>
                    </div>
                </div>

                <!-- Adresses -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Adresse de livraison -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4">Adresse de livraison</h3>
                        <div class="text-slate-600 text-sm space-y-1">
                            <p class="font-medium text-slate-900">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</p>
                            <p>{{ $order->shipping_address }}</p>
                            @if($order->shipping_address2)
                                <p>{{ $order->shipping_address2 }}</p>
                            @endif
                            <p>{{ $order->shipping_postal_code }} {{ $order->shipping_city }}</p>
                            <p>{{ $order->shipping_country }}</p>
                            @if($order->shipping_phone)
                                <p class="mt-2">📞 {{ $order->shipping_phone }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Adresse de facturation -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4">Adresse de facturation</h3>
                        <div class="text-slate-600 text-sm space-y-1">
                            <p class="font-medium text-slate-900">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</p>
                            <p>{{ $order->billing_address }}</p>
                            @if($order->billing_address2)
                                <p>{{ $order->billing_address2 }}</p>
                            @endif
                            <p>{{ $order->billing_postal_code }} {{ $order->billing_city }}</p>
                            <p>{{ $order->billing_country }}</p>
                            @if($order->billing_phone)
                                <p class="mt-2">📞 {{ $order->billing_phone }}</p>
                            @endif
                            @if($order->billing_email)
                                <p>✉️ {{ $order->billing_email }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex gap-4">
                    <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour aux commandes
                    </a>
                    @if($order->payment_status === 'paid')
                        <a href="#" class="inline-flex items-center gap-2 px-4 py-2 text-primary-600 bg-primary-50 rounded-xl hover:bg-primary-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Télécharger la facture
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

