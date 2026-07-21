@extends('layouts.front')

@section('title', 'Confirmation de paiement')

@section('content')

<div class="bg-white border-b border-slate-200">
    <div class="container mx-auto px-4 py-4">
        <nav class="text-sm text-slate-500 flex items-center gap-1.5">
            <a href="{{ route('home') }}" class="hover:text-slate-700 transition-colors">Accueil</a>
            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Confirmation de paiement</span>
        </nav>
    </div>
</div>

<div class="bg-slate-50 min-h-screen py-12">
<div class="container mx-auto px-4">
<div class="max-w-lg mx-auto"
     x-data="paymentChecker({{ $order->id }}, '{{ $order->payment_status }}')"
     x-init="startPolling()">

    {{-- Paiement réussi --}}
    <template x-if="status === 'paid'">
        <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-green-100">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Paiement réussi !</h1>
                <p class="text-slate-500 text-sm mb-6">Votre paiement a été accepté. Votre commande est en cours de traitement.</p>
                <div class="bg-slate-50 rounded-lg border border-slate-200 px-4 py-3 mb-6 inline-block">
                    <p class="text-xs text-slate-500 mb-0.5">Numéro de commande</p>
                    <p class="text-slate-900 font-bold text-lg tracking-wide font-mono">{{ $order->order_number }}</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a :href="redirectUrl || '{{ route('checkout.success', ['order' => $order->id]) }}'"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-sm">
                        Voir ma commande
                    </a>
                    <a href="{{ route('home') }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium rounded-lg transition-colors text-sm bg-white">
                        Continuer mes achats
                    </a>
                </div>
            </div>
        </div>
    </template>

    {{-- Paiement échoué --}}
    <template x-if="status === 'failed'">
        <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-red-100">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Paiement refusé</h1>
                <p class="text-slate-500 text-sm mb-6">Votre paiement n'a pas pu être traité. Veuillez réessayer ou choisir un autre mode de paiement.</p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('checkout.payment', ['order' => $order->id]) }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-sm">
                        Réessayer le paiement
                    </a>
                    <a href="{{ route('cart.index') }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium rounded-lg transition-colors text-sm bg-white">
                        Retour au panier
                    </a>
                </div>
            </div>
        </div>
    </template>

    {{-- Timeout --}}
    <template x-if="timedOut">
        <div>
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-5 border border-slate-200">
                    <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-slate-900 mb-2">Vérification en attente</h1>
                <p class="text-slate-500 text-sm mb-5">Le paiement n'a pas encore été confirmé. Cela peut prendre quelques minutes. Vous recevrez un message dès que le paiement sera validé.</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-6 text-left">
                    <p class="text-sm text-blue-800">Votre commande <strong>{{ $order->order_number }}</strong> est enregistrée. Ne payez pas une seconde fois.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('home') }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-sm">
                        Retour à la boutique
                    </a>
                    <a href="{{ route('order-tracking.index') }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium rounded-lg transition-colors text-sm bg-white">
                        Suivre ma commande
                    </a>
                </div>
            </div>
        </div>
    </template>

    {{-- En attente (vérification) --}}
    <template x-if="status === 'pending' && !timedOut">
        <div>
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-amber-100">
                    <svg class="w-8 h-8 text-amber-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-slate-900 mb-4">Vérification du paiement en cours</h1>

                <div class="bg-slate-50 rounded-lg border border-slate-200 px-4 py-3 mb-5 text-left">
                    <p class="text-sm text-slate-600">Commande : <strong class="text-slate-900">{{ $order->order_number }}</strong></p>
                    <p class="text-sm text-slate-600 mt-1">Montant : <strong class="text-slate-900">{{ format_price($order->total) }}</strong></p>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-5 flex items-start gap-3 text-left">
                    <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="text-amber-800 text-sm">Nous vérifions votre paiement auprès de MoneyFusion. Veuillez patienter.</p>
                </div>

                <div class="flex items-center justify-center gap-2 text-slate-500 text-sm mb-4">
                    <span class="w-2 h-2 bg-amber-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-2 h-2 bg-amber-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-2 h-2 bg-amber-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    <span class="ml-1">Vérification <span x-text="checkCount"></span>...</span>
                </div>

                <div class="w-full bg-slate-200 rounded-full h-1 mb-6">
                    <div class="bg-amber-500 h-1 rounded-full transition-all duration-1000"
                         :style="'width: ' + Math.round(100 - (countdown / 5 * 100)) + '%'"></div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('home') }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium rounded-lg transition-colors text-sm bg-white">
                        Retour à l'accueil
                    </a>
                    <button @click="checkStatus()" :disabled="isChecking"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold rounded-lg transition-colors text-sm">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': isChecking }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span x-text="isChecking ? 'Vérification...' : 'Vérifier maintenant'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
</div>
</div>

@push('scripts')
<script>
function paymentChecker(orderId, initialStatus) {
    return {
        orderId, status: initialStatus,
        isChecking: false, checkCount: 0, maxChecks: 60,
        countdown: 5, redirectUrl: null,
        pollInterval: null, countdownInterval: null, timedOut: false,

        startPolling() {
            if (this.status !== 'pending') return;
            this.checkStatus();
            this.pollInterval = setInterval(() => {
                if (this.checkCount >= this.maxChecks) { this.timedOut = true; this.stopPolling(); return; }
                this.checkStatus();
            }, 5000);
            this.countdownInterval = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) this.countdown = 5;
            }, 1000);
        },

        async checkStatus() {
            if (this.isChecking) return;
            this.isChecking = true; this.checkCount++;
            try {
                const res = await fetch(`/api/orders/${this.orderId}/status`);
                const data = await res.json();
                if (data.is_paid) { this.status = 'paid'; this.redirectUrl = data.redirect_url; this.stopPolling(); }
                else if (data.is_failed) { this.status = 'failed'; this.stopPolling(); }
            } catch(e) { console.error(e); }
            finally { this.isChecking = false; }
        },

        stopPolling() {
            if (this.pollInterval) clearInterval(this.pollInterval);
            if (this.countdownInterval) clearInterval(this.countdownInterval);
        }
    };
}
</script>
@endpush
@endsection
