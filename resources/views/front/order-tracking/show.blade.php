@extends('layouts.front')

@section('title', 'Suivi - Commande #' . $order->order_number)

@section('content')
@php
    $steps = [
        ['key' => 'pending',    'label' => 'Commande reçue',    'date' => $order->created_at],
        ['key' => 'confirmed',  'label' => 'Paiement confirmé', 'date' => $order->paid_at ?? ($order->payment_status === 'paid' ? $order->updated_at : null)],
        ['key' => 'processing', 'label' => 'En préparation',    'date' => null],
        ['key' => 'shipped',    'label' => 'Expédiée',          'date' => $order->shipped_at],
        ['key' => 'delivered',  'label' => 'Livrée',            'date' => $order->delivered_at],
    ];
    $statusOrder = [
        'pending' => 0, 'confirmed' => 1, 'processing' => 2,
        'shipped' => 3, 'delivered' => 4,
        'cancelled' => -1, 'refunded' => -1,
    ];
    $currentIndex = $statusOrder[$order->status] ?? 0;
    $isCancelled  = in_array($order->status, ['cancelled', 'refunded']);
    $statusBadge  = match($order->status) {
        'delivered'            => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'shipped'              => 'bg-blue-50 text-blue-700 border-blue-200',
        'processing'           => 'bg-amber-50 text-amber-700 border-amber-200',
        'confirmed'            => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'cancelled','refunded' => 'bg-red-50 text-red-700 border-red-200',
        default                => 'bg-slate-50 text-slate-700 border-slate-200',
    };
@endphp

{{-- Breadcrumb --}}
<div class="bg-white border-b border-slate-100">
    <div class="container mx-auto px-4 py-3">
        <nav class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-slate-900 transition-colors">Accueil</a>
            <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('order-tracking.index') }}" class="hover:text-slate-900 transition-colors">Suivi de commande</a>
            <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">#{{ $order->order_number }}</span>
        </nav>
    </div>
</div>

<div class="container mx-auto px-4 py-8 max-w-3xl">

    {{-- Header --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 mb-5">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusBadge }} mb-2">
                    {{ $order->status_label }}
                </span>
                <h1 class="text-xl font-bold text-slate-900">Commande #{{ $order->order_number }}</h1>
                <p class="text-sm text-slate-500 mt-0.5">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div class="sm:text-right">
                <p class="text-2xl font-bold text-slate-900">{{ format_price($order->total) }}</p>
                <p class="text-xs text-slate-500">{{ $order->items->sum('quantity') }} article{{ $order->items->sum('quantity') > 1 ? 's' : '' }}</p>
            </div>
        </div>
    </div>

    {{-- Cancelled state --}}
    @if($isCancelled)
    <div class="bg-red-50 border border-red-200 rounded-2xl p-5 mb-5 text-center">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <h3 class="font-bold text-red-900 mb-1">Commande annulée</h3>
        <p class="text-sm text-red-700">Cette commande a été annulée. Contactez-nous pour toute question.</p>
        @if($order->cancellation_reason ?? false)
        <p class="text-xs text-red-600 mt-2">Raison : {{ $order->cancellation_reason }}</p>
        @endif
    </div>
    @endif

    {{-- Timeline --}}
    @if(!$isCancelled)
    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 mb-5">
        <h2 class="text-sm font-bold text-slate-900 mb-5">Progression de la commande</h2>
        @foreach($steps as $i => $step)
        @php
            $isDone    = $i < $currentIndex;
            $isCurrent = $i === $currentIndex;
            $isFuture  = $i > $currentIndex;
        @endphp
        <div class="flex gap-3">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                    {{ $isDone    ? 'bg-emerald-500 text-white' : '' }}
                    {{ $isCurrent ? 'bg-blue-600 text-white' : '' }}
                    {{ $isFuture  ? 'bg-slate-100 text-slate-400 border border-slate-200' : '' }}">
                    @if($isDone)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @else
                        <span class="text-xs font-bold">{{ $i + 1 }}</span>
                    @endif
                </div>
                @if(!$loop->last)
                <div class="w-0.5 flex-1 my-1 {{ $isDone ? 'bg-emerald-300' : 'bg-slate-200' }}"></div>
                @endif
            </div>
            <div class="flex-1 {{ !$loop->last ? 'pb-4' : 'pb-0' }}">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold
                        {{ $isDone    ? 'text-slate-600' : '' }}
                        {{ $isCurrent ? 'text-blue-700' : '' }}
                        {{ $isFuture  ? 'text-slate-400' : '' }}">
                        {{ $step['label'] }}
                    </p>
                    @if($step['date'] && ($isDone || $isCurrent))
                    <span class="text-xs text-slate-400">{{ $step['date']->format('d/m H:i') }}</span>
                    @endif
                </div>
                @if($isCurrent)
                <p class="text-xs text-blue-500 mt-0.5">En cours...</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Delivery address --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 mb-5">
        <h2 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Adresse de livraison
        </h2>
        <div class="text-sm text-slate-600 space-y-0.5">
            <p class="font-medium text-slate-900">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</p>
            <p>{{ $order->shipping_address }}</p>
            <p>{{ $order->shipping_city }}{{ $order->shipping_country ? ', ' . $order->shipping_country : '' }}</p>
            @if($order->shipping_phone)
            <p class="text-slate-500 pt-1 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                {{ $order->shipping_phone }}
            </p>
            @endif
        </div>
    </div>

    {{-- Order items --}}
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900">Articles ({{ $order->items->count() }})</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($order->items as $item)
            <div class="flex gap-3 p-4">
                <div class="w-14 h-14 bg-slate-100 rounded-xl overflow-hidden flex-shrink-0">
                    @if($item->product && $item->product->images && $item->product->images->first())
                        <img src="{{ asset('storage/' . $item->product->images->first()->path) }}" alt="{{ $item->name }}" class="w-full h-full object-cover" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-slate-900 text-sm truncate">{{ $item->product?->name ?? $item->name }}</p>
                    @if($item->variant_name)
                    <p class="text-xs text-slate-500">{{ $item->variant_name }}</p>
                    @endif
                    <p class="text-xs text-slate-400 mt-1">Qté : {{ $item->quantity }} × {{ format_price($item->unit_price) }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="font-bold text-sm text-slate-900">{{ format_price($item->total) }}</p>
                </div>
            </div>
            @endforeach
        </div>
        {{-- Totals --}}
        <div class="bg-slate-50 px-5 py-4 space-y-2 border-t border-slate-100">
            <div class="flex justify-between text-sm text-slate-600">
                <span>Sous-total</span>
                <span>{{ format_price($order->subtotal) }}</span>
            </div>
            <div class="flex justify-between text-sm text-slate-600">
                <span>Livraison</span>
                @if($order->shipping_amount > 0)
                <span>{{ format_price($order->shipping_amount) }}</span>
                @else
                <span class="text-emerald-600 font-medium">Gratuite</span>
                @endif
            </div>
            @if($order->discount_amount > 0)
            <div class="flex justify-between text-sm text-emerald-600">
                <span>Réduction</span>
                <span>-{{ format_price($order->discount_amount) }}</span>
            </div>
            @endif
            <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                <span class="font-bold text-slate-900">Total</span>
                <span class="text-xl font-bold text-slate-900">{{ format_price($order->total) }}</span>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('app.whatsapp_number', '2250506805382')) }}?text={{ urlencode('Bonjour, j\'ai besoin d\'aide pour ma commande #' . $order->order_number) }}"
           target="_blank" rel="noopener"
           class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-[#25D366] text-white text-sm font-semibold rounded-xl hover:opacity-90 transition-opacity">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Besoin d'aide ?
        </a>
        <a href="{{ route('order-tracking.index') }}"
           class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-white border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
    </div>

</div>
@endsection
