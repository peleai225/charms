@extends('layouts.front')

@section('title', 'Paiement')

@section('content')
<!-- Hero header -->
<div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white py-10">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-3 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <span class="text-white">Paiement</span>
        </nav>
        <h1 class="text-3xl font-bold">Paiement</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-lg mx-auto">

        @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Order summary header -->
            <div class="p-6 bg-gradient-to-r from-primary-50 to-white border-b border-slate-100">
                <p class="text-sm text-slate-500">Commande</p>
                <p class="font-bold text-lg text-slate-900">{{ $order->order_number }}</p>
                <p class="text-2xl font-extrabold text-primary-600 mt-2">{{ number_format($order->total, 0, ',', ' ') }} F CFA</p>
            </div>

            <!-- Payment options -->
            <div class="p-6 space-y-4">
                <h2 class="font-semibold text-slate-900">Choisissez votre mode de paiement</h2>

                <!-- CinetPay -->
                <form action="{{ route('checkout.process-payment', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="method" value="cinetpay">
                    <button type="submit" class="w-full flex items-center justify-between p-4 border-2 border-orange-200 rounded-2xl hover:border-orange-400 hover:bg-orange-50 hover:shadow-md transition-all duration-300 group">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="font-medium text-slate-900">Mobile Money / Carte</p>
                                <p class="text-sm text-slate-500">Orange, MTN, Wave, Visa...</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-orange-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>

                <!-- Lygos Pay -->
                <form action="{{ route('checkout.process-payment', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="method" value="lygos">
                    <button type="submit" class="w-full flex items-center justify-between p-4 border-2 border-blue-200 rounded-2xl hover:border-blue-400 hover:bg-blue-50 hover:shadow-md transition-all duration-300 group">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="font-medium text-slate-900">Lygos Pay</p>
                                <p class="text-sm text-slate-500">Mobile Money et paiements internationaux</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>

                <!-- COD -->
                <form action="{{ route('checkout.process-payment', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="method" value="cod">
                    <button type="submit" class="w-full flex items-center justify-between p-4 border-2 border-green-200 rounded-2xl hover:border-green-400 hover:bg-green-50 hover:shadow-md transition-all duration-300 group">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="font-medium text-slate-900">Paiement à la livraison</p>
                                <p class="text-sm text-slate-500">Payez en espèces</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-green-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Security footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-500 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Paiement 100% sécurisé
                </p>
            </div>
        </div>

        <p class="text-center mt-6">
            <a href="{{ route('cart.index') }}" class="text-slate-500 hover:text-primary-600 text-sm transition-colors">
                Annuler et retourner au panier
            </a>
        </p>
    </div>
</div>
@endsection
