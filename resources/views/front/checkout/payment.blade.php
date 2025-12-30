@extends('layouts.front')

@section('title', 'Paiement')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-lg mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Paiement</h1>

        @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <!-- Résumé commande -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <p class="text-sm text-gray-500">Commande</p>
                <p class="font-bold text-lg">{{ $order->order_number }}</p>
                <p class="text-2xl font-bold text-primary-600 mt-2">{{ number_format($order->total, 0, ',', ' ') }} F CFA</p>
            </div>

            <!-- Options de paiement -->
            <div class="space-y-4">
                <h2 class="font-semibold text-gray-900">Choisissez votre mode de paiement</h2>

                <!-- Bouton CinetPay -->
                <form action="{{ route('checkout.process-payment', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="method" value="cinetpay">
                    <button type="submit" class="w-full flex items-center justify-between p-4 border-2 border-orange-200 rounded-xl hover:border-orange-400 hover:bg-orange-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="font-medium text-gray-900">Mobile Money / Carte</p>
                                <p class="text-sm text-gray-500">Orange, MTN, Wave, Visa...</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </form>

                <!-- Paiement à la livraison -->
                <form action="{{ route('checkout.process-payment', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="method" value="cod">
                    <button type="submit" class="w-full flex items-center justify-between p-4 border-2 border-green-200 rounded-xl hover:border-green-400 hover:bg-green-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="font-medium text-gray-900">Paiement à la livraison</p>
                                <p class="text-sm text-gray-500">Payez en espèces</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Sécurité -->
            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-500 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Paiement sécurisé
                </p>
            </div>
        </div>

        <p class="text-center mt-6">
            <a href="{{ route('cart.index') }}" class="text-gray-500 hover:text-primary-600 text-sm">
                Annuler et retourner au panier
            </a>
        </p>
    </div>
</div>
@endsection

