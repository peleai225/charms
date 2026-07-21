@extends('layouts.front')

@section('title', 'Mes commandes')

@section('content')
@php
    $customer = auth()->user()->customer;
    $orders = $customer
        ? $customer->orders()->with('items.product')->latest()->paginate(10)
        : collect();
@endphp

<div class="bg-slate-50 border-b border-slate-200 py-8">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-2 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-slate-700 transition-colors">Accueil</a>
            <span>/</span>
            <a href="{{ route('account.dashboard') }}" class="hover:text-slate-700 transition-colors">Mon compte</a>
            <span>/</span>
            <span class="text-slate-700">Mes commandes</span>
        </nav>
        <h1 class="text-2xl font-bold text-slate-900">Mes commandes</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('front.account.partials.sidebar')

            <div class="flex-1 min-w-0">

                @if($customer && $orders->count() > 0)
                <div class="space-y-4">
                    @foreach($orders as $order)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-blue-200 transition-colors">

                        {{-- En-tête commande --}}
                        <div class="px-5 py-4 bg-slate-50 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">#{{ $order->order_number }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ $order->created_at->format('d/m/Y à H:i') }}
                                    &middot; {{ $order->items->sum('quantity') }} article(s)
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($order->status === 'delivered') bg-green-100 text-green-700
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-700
                                    @elseif($order->status === 'shipped') bg-indigo-100 text-indigo-700
                                    @elseif($order->status === 'processing') bg-blue-100 text-blue-700
                                    @else bg-amber-100 text-amber-700 @endif">
                                    {{ $order->status_label }}
                                </span>
                                <a href="{{ route('account.orders.show', $order) }}"
                                   class="text-sm text-blue-600 font-medium hover:text-blue-700 transition-colors">
                                    Détails
                                </a>
                            </div>
                        </div>

                        {{-- Aperçu articles --}}
                        <div class="px-5 py-4">
                            <div class="flex gap-3 flex-wrap">
                                @foreach($order->items->take(3) as $item)
                                <div class="flex items-center gap-2.5">
                                    <div class="w-12 h-12 bg-slate-100 rounded-lg overflow-hidden flex-shrink-0">
                                        @if($item->product && $item->product->primary_image_url)
                                            <img src="{{ $item->product->primary_image_url }}"
                                                 alt="{{ $item->product->name }}"
                                                 class="w-full h-full object-cover" loading="lazy">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-slate-800 line-clamp-1 max-w-[120px]">
                                            {{ $item->product?->name ?? 'Produit supprimé' }}
                                        </p>
                                        <p class="text-xs text-slate-400">x{{ $item->quantity }}</p>
                                    </div>
                                </div>
                                @endforeach
                                @if($order->items->count() > 3)
                                    <div class="flex items-center">
                                        <span class="text-xs text-slate-400">+{{ $order->items->count() - 3 }} autre(s)</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-sm text-slate-500">Total</span>
                                <span class="font-bold text-slate-900">{{ number_format($order->total, 0, ',', ' ') }} F CFA</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($orders->hasPages())
                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
                @endif

                @else
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Vous n'avez pas encore passé de commande</h3>
                    <p class="text-slate-500 text-sm mb-6">Découvrez notre catalogue et trouvez ce qu'il vous faut.</p>
                    <a href="{{ route('shop.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors">
                        Découvrir la boutique
                    </a>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
