@extends('layouts.front')

@section('title', 'Commande confirmée')

@section('content')
<!-- Hero header -->
<div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white py-10">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-3 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <span class="text-white">Confirmation</span>
        </nav>
        <h1 class="text-3xl font-bold">Commande confirmée</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto text-center">
        <!-- Success icon -->
        <div class="w-24 h-24 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl shadow-green-200/50">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h2 class="text-3xl font-extrabold text-slate-900 mb-4">Merci pour votre commande !</h2>
        <p class="text-slate-600 mb-6 text-lg">
            Votre commande <strong class="text-primary-600">{{ $order->order_number }}</strong> a été enregistrée avec succès.
        </p>

        @if($order->payment_status === 'paid')
            <div class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-100 text-green-700 rounded-full text-sm font-semibold mb-6">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Paiement confirmé
            </div>
        @elseif($order->payment_method === 'cod')
            <div class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold mb-6">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                </svg>
                Paiement à la livraison
            </div>
        @else
            <div class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-100 text-amber-700 rounded-full text-sm font-semibold mb-6">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Paiement en attente
            </div>
        @endif

        <!-- Order details card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 text-left mt-6 hover:shadow-lg transition-shadow duration-300">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Détails de la commande</h3>

            <div class="space-y-3 mb-4">
                @foreach($order->items as $item)
                <div class="flex justify-between items-center py-2 border-b border-slate-100 last:border-0">
                    <div>
                        <p class="font-medium text-slate-900">{{ $item->product_name }}</p>
                        @if($item->variant_name)
                            <p class="text-sm text-slate-500">{{ $item->variant_name }}</p>
                        @endif
                        <p class="text-sm text-slate-500">Qté: {{ $item->quantity }}</p>
                    </div>
                    <p class="font-medium text-slate-900">{{ number_format($item->total, 0, ',', ' ') }} F</p>
                </div>
                @endforeach
            </div>

            <div class="border-t border-slate-200 pt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-600">Sous-total</span>
                    <span class="text-slate-900">{{ number_format($order->subtotal, 0, ',', ' ') }} F</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-green-600">
                    <span>Réduction</span>
                    <span>-{{ number_format($order->discount_amount, 0, ',', ' ') }} F</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-600">Livraison</span>
                    <span class="text-slate-900">{{ $order->shipping_amount > 0 ? number_format($order->shipping_amount, 0, ',', ' ') . ' F' : 'Gratuite' }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-200">
                    <span class="text-slate-900">Total</span>
                    <span class="text-primary-600">{{ number_format($order->total, 0, ',', ' ') }} F CFA</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-200">
                <h4 class="font-medium text-slate-900 mb-2">Adresse de livraison</h4>
                <p class="text-sm text-slate-600">
                    {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                    {{ $order->shipping_address }}<br>
                    @if($order->shipping_address_2){{ $order->shipping_address_2 }}<br>@endif
                    {{ $order->shipping_postal_code }} {{ $order->shipping_city }}<br>
                    {{ $order->shipping_country }}
                </p>
            </div>
        </div>

        <p class="text-sm text-slate-500 mt-6">
            Un email de confirmation a été envoyé à <strong>{{ $order->billing_email }}</strong>
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
            <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/25 hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                Continuer mes achats
            </a>
            @auth
            <a href="{{ route('account.orders') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Voir mes commandes
            </a>
            @endauth
        </div>
    </div>
</div>
@endsection
