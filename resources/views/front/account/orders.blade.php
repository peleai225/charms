@extends('layouts.front')

@section('title', 'Mes commandes')

@section('content')
<!-- Hero header -->
<div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white py-10">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-3 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('account.dashboard') }}" class="hover:text-white transition-colors">Mon compte</a>
            <span class="text-slate-600">/</span>
            <span class="text-white">Mes commandes</span>
        </nav>
        <h1 class="text-3xl font-bold">Mes commandes</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            @include('front.account.partials.sidebar')

            <!-- Main Content -->
            <div class="flex-1">
                <h2 class="text-xl font-bold text-slate-900 mb-6">Historique de commandes</h2>
                
                @php
                    $customer = auth()->user()->customer;
                    $orders = $customer ? $customer->orders()->with('items.product')->latest()->paginate(10) : collect();
                @endphp

                @if($customer && $orders->count() > 0)
                    <div class="space-y-4">
                        @foreach($orders as $order)
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <!-- Order header -->
                            <div class="p-6 bg-slate-50 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900">#{{ $order->order_number }}</p>
                                    <p class="text-sm text-slate-500">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium
                                        @if($order->status === 'delivered') bg-green-100 text-green-800
                                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                        @elseif($order->status === 'shipped') bg-blue-100 text-blue-800
                                        @else bg-amber-100 text-amber-800 @endif">
                                        {{ $order->status_label }}
                                    </span>
                                    <a href="{{ route('account.orders.show', $order) }}" class="text-primary-600 hover:underline text-sm font-medium">
                                        Voir détails →
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Order items preview -->
                            <div class="p-6">
                                <div class="flex flex-wrap gap-4">
                                    @foreach($order->items->take(3) as $item)
                                    <div class="flex items-center gap-3">
                                        <div class="w-16 h-16 bg-slate-100 rounded-lg overflow-hidden flex-shrink-0">
                                            @if($item->product && $item->product->primary_image_url)
                                                <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-slate-900">{{ $item->product?->name ?? 'Produit supprimé' }}</p>
                                            <p class="text-xs text-slate-500">Qté: {{ $item->quantity }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                    @if($order->items->count() > 3)
                                        <div class="flex items-center">
                                            <span class="text-sm text-slate-500">+{{ $order->items->count() - 3 }} autre(s)</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                                    <p class="text-sm text-slate-600">{{ $order->items->sum('quantity') }} article(s)</p>
                                    <p class="text-lg font-bold text-slate-900">{{ number_format($order->total, 0, ',', ' ') }} F</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $orders->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-2xl p-12 shadow-sm border border-slate-200 text-center">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900 mb-2">Aucune commande</h3>
                        <p class="text-slate-600 mb-6">Vous n'avez pas encore passé de commande.</p>
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-medium rounded-xl hover:bg-primary-700 transition-colors">
                            Découvrir nos produits
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

