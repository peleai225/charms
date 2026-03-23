@extends('layouts.front')

@section('title', 'Confirmation de paiement')

@section('content')
<!-- Hero header -->
<div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white py-10">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-3 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <span class="text-white">Confirmation de paiement</span>
        </nav>
        <h1 class="text-3xl font-bold">Confirmation de paiement</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-lg mx-auto text-center"
         x-data="paymentChecker({{ $order->id }}, '{{ $order->payment_status }}')"
         x-init="startPolling()">
        
        <!-- État: Paiement réussi -->
        <template x-if="status === 'paid'">
            <div x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100">
                
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">Paiement réussi !</h1>
                <p class="text-gray-600 mb-6">
                    Votre paiement a été accepté. Votre commande <strong>{{ $order->order_number }}</strong> est en cours de traitement.
                </p>

                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                    <div class="flex items-center justify-center gap-2 text-green-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span class="font-medium">Transaction sécurisée vérifiée</span>
                    </div>
                </div>

                <a :href="redirectUrl || '{{ route('checkout.success', ['order' => $order->id]) }}'" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    Voir ma commande
                </a>
            </div>
        </template>

        <!-- État: Paiement échoué -->
        <template x-if="status === 'failed'">
            <div x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100">
                
                <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">Paiement refusé</h1>
                <p class="text-gray-600 mb-6">
                    Votre paiement n'a pas pu être traité. Veuillez réessayer ou choisir un autre mode de paiement.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('checkout.payment', ['order' => $order->id]) }}" 
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
                        Réessayer le paiement
                    </a>
                    <a href="{{ route('cart.index') }}" 
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors">
                        Retour au panier
                    </a>
                </div>
            </div>
        </template>

        <!-- État: En attente -->
        <template x-if="status === 'pending'">
            <div>
                <!-- Icône animée -->
                <div class="w-24 h-24 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6 relative">
                    <svg class="w-12 h-12 text-amber-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <!-- Pulse indicator -->
                    <span class="absolute -top-1 -right-1 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500"></span>
                    </span>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">Vérification du paiement en cours</h1>
                
                <!-- Message de sécurité -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <div class="text-left">
                            <p class="text-amber-800 font-medium">Sécurité du paiement</p>
                            <p class="text-amber-700 text-sm mt-1">
                                Nous vérifions votre paiement directement auprès de CinetPay pour garantir la sécurité de votre transaction.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Informations commande -->
                <div class="bg-gray-50 rounded-xl p-4 mb-6">
                    <p class="text-gray-600 text-sm">
                        Commande : <strong class="text-gray-900">{{ $order->order_number }}</strong>
                    </p>
                    <p class="text-gray-600 text-sm mt-1">
                        Montant : <strong class="text-gray-900">{{ format_price($order->total) }}</strong>
                    </p>
                </div>

                <!-- Indicateur de vérification -->
                <div class="flex items-center justify-center gap-2 text-gray-500 text-sm mb-6">
                    <div class="flex gap-1">
                        <span class="w-2 h-2 bg-amber-500 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="w-2 h-2 bg-amber-500 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-2 h-2 bg-amber-500 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                    <span>Vérification <span x-text="checkCount"></span> en cours...</span>
                </div>

                <!-- Barre de progression -->
                <div class="w-full bg-gray-200 rounded-full h-1.5 mb-6">
                    <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-1000" 
                         :style="'width: ' + (100 - (countdown / 5 * 100)) + '%'"></div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('home') }}" 
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors">
                        Retour à l'accueil
                    </a>
                    <button @click="checkStatus()" 
                            :disabled="isChecking"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white font-semibold rounded-xl transition-colors">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': isChecking }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span x-text="isChecking ? 'Vérification...' : 'Vérifier maintenant'"></span>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function paymentChecker(orderId, initialStatus) {
    return {
        orderId: orderId,
        status: initialStatus,
        isChecking: false,
        checkCount: 0,
        countdown: 5,
        redirectUrl: null,
        pollInterval: null,
        countdownInterval: null,

        startPolling() {
            if (this.status !== 'pending') return;
            
            // Vérifier immédiatement
            this.checkStatus();
            
            // Puis toutes les 5 secondes
            this.pollInterval = setInterval(() => {
                this.checkStatus();
            }, 5000);

            // Compte à rebours visuel
            this.countdownInterval = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    this.countdown = 5;
                }
            }, 1000);
        },

        async checkStatus() {
            if (this.isChecking) return;
            
            this.isChecking = true;
            this.checkCount++;
            
            try {
                const response = await fetch(`/api/orders/${this.orderId}/status`);
                const data = await response.json();
                
                if (data.is_paid) {
                    this.status = 'paid';
                    this.redirectUrl = data.redirect_url;
                    this.stopPolling();
                    
                    // Notification sonore (optionnel)
                    this.playSuccessSound();
                    
                } else if (data.is_failed) {
                    this.status = 'failed';
                    this.stopPolling();
                }
            } catch (error) {
                console.error('Erreur lors de la vérification:', error);
            } finally {
                this.isChecking = false;
            }
        },

        stopPolling() {
            if (this.pollInterval) clearInterval(this.pollInterval);
            if (this.countdownInterval) clearInterval(this.countdownInterval);
        },

        playSuccessSound() {
            // Son de succès optionnel
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleVQxIHi07PGGUhMC');
                audio.volume = 0.3;
                audio.play().catch(() => {});
            } catch(e) {}
        }
    }
}
</script>
@endpush
@endsection
