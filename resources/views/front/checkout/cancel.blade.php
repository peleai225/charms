@extends('layouts.front')

@section('title', 'Paiement annulé')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-lg mx-auto text-center">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-4">Paiement annulé</h1>
        <p class="text-gray-600 mb-6">
            Vous avez annulé le paiement. Votre commande n'a pas été finalisée.
        </p>

        @if($order)
        <p class="text-sm text-gray-500 mb-6">
            Référence : <strong>{{ $order->order_number }}</strong>
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('checkout.payment', ['order' => $order->id]) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Réessayer le paiement
            </a>
            <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors">
                Retour au panier
            </a>
        </div>
        @else
        <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
            Retour au panier
        </a>
        @endif
    </div>
</div>
@endsection

