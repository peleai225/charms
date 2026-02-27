@extends('layouts.front')

@section('title', 'Suivi - Commande #' . $order->order_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <nav class="text-sm text-slate-500 mb-8">
        <a href="{{ route('home') }}" class="hover:text-primary-600">Accueil</a>
        <span class="mx-2">/</span>
        <a href="{{ route('order-tracking.index') }}" class="hover:text-primary-600">Suivi de commande</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900">#{{ $order->order_number }}</span>
    </nav>

    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-slate-900">Commande #{{ $order->order_number }}</h1>
            <span class="inline-flex px-4 py-2 rounded-full text-sm font-medium
                @if($order->status === 'delivered') bg-green-100 text-green-800
                @elseif($order->status === 'cancelled' || $order->status === 'refunded') bg-red-100 text-red-800
                @elseif($order->status === 'shipped') bg-blue-100 text-blue-800
                @else bg-amber-100 text-amber-800 @endif">
                {{ $order->status_label }}
            </span>
        </div>

        <!-- Timeline du suivi -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-6">Suivi de votre commande</h2>
            <div class="relative">
                <div class="space-y-6">
                    @php
                        $steps = [
                            ['key' => 'pending', 'label' => 'Commande reçue', 'date' => $order->created_at, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['key' => 'confirmed', 'label' => 'Paiement confirmé', 'date' => $order->paid_at ?? ($order->payment_status === 'paid' ? $order->updated_at : null), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['key' => 'processing', 'label' => 'En préparation', 'date' => null, 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517L8.574 16.6a2 2 0 01-1.148.54l-2.514.34a1 1 0 00-.714.493l-1.02 1.68a1 1 0 00.493 1.395l1.68.84a1 1 0 001.395-.493l.34-.68'],
                            ['key' => 'shipped', 'label' => 'Expédiée', 'date' => $order->shipped_at, 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
                            ['key' => 'delivered', 'label' => 'Livrée', 'date' => $order->delivered_at, 'icon' => 'M5 13l4 4L19 7'],
                        ];
                        $statusOrder = ['pending' => 0, 'confirmed' => 1, 'processing' => 2, 'shipped' => 3, 'delivered' => 4, 'cancelled' => -1, 'refunded' => -1];
                        $currentIndex = $statusOrder[$order->status] ?? 0;
                    @endphp
                    @foreach($steps as $i => $step)
                        @if(($order->status === 'cancelled' || $order->status === 'refunded') && $step['key'] !== 'pending')
                            @continue
                        @endif
                        @php $isDone = $i <= $currentIndex && $currentIndex >= 0; @endphp
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                                {{ $isDone ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-400' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
                                </svg>
                            </div>
                            <div class="flex-1 pb-6 {{ !$loop->last ? 'border-l-2 border-slate-200 ml-5 -translate-x-5 pl-10' : '' }}">
                                <p class="font-medium {{ $isDone ? 'text-slate-900' : 'text-slate-500' }}">{{ $step['label'] }}</p>
                                @if($step['date'])
                                    <p class="text-sm text-slate-500">{{ $step['date']->format('d/m/Y à H:i') }}</p>
                                @elseif($isDone && $step['key'] === 'processing')
                                    <p class="text-sm text-slate-500">En cours</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Numéro de suivi -->
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
                    <p class="font-semibold text-blue-900 text-lg">{{ $order->tracking_number }}</p>
                    @if($order->shipping_carrier)
                        <p class="text-sm text-blue-700">Transporteur : {{ $order->shipping_carrier }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Résumé commande -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-900">Articles commandés</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                <div class="p-6 flex gap-4">
                    <div class="w-16 h-16 bg-slate-100 rounded-lg overflow-hidden flex-shrink-0">
                        @if($item->product && $item->product->primary_image_url)
                            <img src="{{ $item->product->primary_image_url }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-slate-900">{{ $item->product?->name ?? $item->name }}</p>
                        @if($item->variant_name)
                            <p class="text-sm text-slate-500">{{ $item->variant_name }}</p>
                        @endif
                        <p class="text-sm text-slate-500">Quantité : {{ $item->quantity }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-slate-900">{{ number_format($item->total, 0, ',', ' ') }} F</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="p-6 bg-slate-50">
                <div class="flex justify-between text-lg font-bold">
                    <span>Total</span>
                    <span>{{ number_format($order->total, 0, ',', ' ') }} F CFA</span>
                </div>
            </div>
        </div>

        <a href="{{ route('order-tracking.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Nouvelle recherche
        </a>
    </div>
</div>
@endsection
