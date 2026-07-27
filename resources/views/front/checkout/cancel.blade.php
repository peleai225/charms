@extends('layouts.front')

@section('title', 'Commande annulée')

@section('content')

{{-- Breadcrumb --}}
<div class="bg-white border-b border-slate-100">
    <div class="container mx-auto px-4 py-3">
        <nav class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-slate-900 transition-colors">Accueil</a>
            <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Commande annulée</span>
        </nav>
    </div>
</div>

<div class="min-h-[70vh] flex items-center bg-slate-50 py-12 px-4">
    <div class="max-w-md mx-auto w-full text-center">

        {{-- Icon --}}
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-slate-900 mb-2">Commande annulée</h1>
        <p class="text-slate-500 text-sm mb-1">Votre commande n'a pas été finalisée.</p>
        <p class="text-slate-500 text-sm mb-6">Votre panier est conservé — vous pouvez réessayer quand vous le souhaitez.</p>

        @if($order)
        <p class="text-xs text-slate-400 mb-6">
            Référence : <span class="font-semibold text-slate-600">{{ $order->order_number }}</span>
        </p>
        @endif

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center mb-8">
            @if($order)
            <a href="{{ route('checkout.index') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Réessayer
            </a>
            @endif
            <a href="{{ route('shop.index') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-white border border-slate-200 text-slate-700 font-medium text-sm rounded-xl hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Continuer mes achats
            </a>
        </div>

        {{-- Aide --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-5 text-center shadow-sm">
            <p class="text-sm text-slate-600 mb-3">Un problème lors du paiement ? Notre équipe peut vous aider.</p>
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('app.whatsapp_number', '2250506805382')) }}?text={{ urlencode('Bonjour, j\'ai un problème lors du paiement de ma commande' . ($order ? ' #' . $order->order_number : '') . '.') }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#25D366] text-white text-sm font-medium rounded-xl hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Contacter le support WhatsApp
            </a>
        </div>

    </div>
</div>
@endsection
