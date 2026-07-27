@extends('layouts.front')

@section('title', 'Mon compte')

@section('content')
@php
    $customer = auth()->user()->customer;
    $ordersCount = $customer ? $customer->orders()->count() : 0;
    $pendingCount = $customer ? $customer->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count() : 0;
    $loyaltyPoints = $customer?->loyalty_points ?? 0;
    $wishlistCount = $customer ? \App\Models\Wishlist::where('customer_id', $customer->id)->count() : 0;
    $recentOrders = $customer ? $customer->orders()->with('items.product')->latest()->take(3)->get() : collect();
    $defaultAddress = $customer ? $customer->addresses()->where('is_default', true)->orWhere('type', 'shipping')->first() : null;
@endphp

<div class="bg-slate-50 border-b border-slate-200 py-8">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-2 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-slate-700 transition-colors">Accueil</a>
            <span>/</span>
            <span class="text-slate-700">Mon compte</span>
        </nav>
        <h1 class="text-2xl font-bold text-slate-900">Bonjour, {{ auth()->user()->name }}</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('front.account.partials.sidebar')

            <div class="flex-1 min-w-0">

                @if (session('success'))
                <div class="mb-5 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
                    {{ session('success') }}
                </div>
                @endif

                {{-- Stats cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-2xl p-4 border border-slate-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ $ordersCount }}</p>
                                <p class="text-xs text-slate-500">Commandes</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-slate-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ number_format($loyaltyPoints) }}</p>
                                <p class="text-xs text-slate-500">Points fidélité</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 border border-slate-200 col-span-2 sm:col-span-1">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ $wishlistCount }}</p>
                                <p class="text-xs text-slate-500">Liste de souhaits</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dernières commandes --}}
                @if($recentOrders->count() > 0)
                <div class="bg-white rounded-2xl border border-slate-200 mb-6 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-semibold text-slate-900">Dernières commandes</h2>
                        <a href="{{ route('account.orders') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                            Voir tout
                        </a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($recentOrders as $order)
                        <div class="px-5 py-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-slate-900 text-sm">#{{ $order->order_number }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $order->created_at->format('d/m/Y') }} &middot; {{ $order->items->count() }} article(s)</p>
                            </div>
                            <div class="text-right flex items-center gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900 text-sm">{{ number_format($order->total, 0, ',', ' ') }} F</p>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($order->status === 'delivered') bg-green-100 text-green-700
                                        @elseif($order->status === 'cancelled') bg-red-100 text-red-700
                                        @elseif($order->status === 'shipped') bg-primary-100 text-primary-700
                                        @elseif($order->status === 'processing') bg-primary-100 text-primary-700
                                        @else bg-amber-100 text-amber-700 @endif">
                                        {{ $order->status_label }}
                                    </span>
                                </div>
                                <a href="{{ route('account.orders.show', $order) }}"
                                   class="text-slate-400 hover:text-primary-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center mb-6">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-slate-500 text-sm mb-4">Vous n'avez pas encore passé de commande.</p>
                    <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
                        Découvrir la boutique
                    </a>
                </div>
                @endif

                {{-- Adresse principale --}}
                <div class="bg-white rounded-2xl border border-slate-200">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-semibold text-slate-900">Adresse principale</h2>
                        <a href="{{ route('account.addresses') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                            Gérer
                        </a>
                    </div>
                    <div class="px-5 py-4">
                        @if($defaultAddress)
                        <div class="text-sm text-slate-600 space-y-0.5">
                            <p class="font-medium text-slate-900">{{ $defaultAddress->first_name }} {{ $defaultAddress->last_name }}</p>
                            <p>{{ $defaultAddress->address_line1 }}</p>
                            <p>{{ $defaultAddress->postal_code }} {{ $defaultAddress->city }}</p>
                            @if($defaultAddress->phone)
                                <p class="mt-1 text-slate-500">{{ $defaultAddress->phone }}</p>
                            @endif
                        </div>
                        @else
                        <div class="text-center py-4">
                            <p class="text-sm text-slate-500 mb-3">Aucune adresse enregistrée</p>
                            <a href="{{ route('account.addresses') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-primary-600 text-primary-600 text-sm font-medium rounded-xl hover:bg-primary-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Ajouter une adresse
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
