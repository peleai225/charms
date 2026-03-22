@extends('layouts.front')

@section('title', 'Mon compte')

@section('content')
<!-- Hero header -->
<div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white py-10">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-3 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <span class="text-white">Mon compte</span>
        </nav>
        <h1 class="text-3xl font-bold">Mon compte</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            @include('front.account.partials.sidebar')

            <!-- Main Content -->
            <div class="flex-1">
                <div class="bg-gradient-to-r from-primary-50 to-white rounded-2xl p-6 border border-primary-100/50 mb-6">
                    <h2 class="text-xl font-bold text-slate-900 mb-1">
                        Bonjour {{ auth()->user()->name }} !
                    </h2>
                    <p class="text-slate-600">
                        Depuis votre tableau de bord, vous pouvez consulter vos
                        <a href="{{ route('account.orders') }}" class="text-primary-600 hover:underline font-medium">commandes</a>,
                        gérer vos <a href="{{ route('account.addresses') }}" class="text-primary-600 hover:underline font-medium">adresses</a>,
                        et modifier vos informations.
                    </p>
                </div>

                <!-- Stats rapides -->
                <div class="grid md:grid-cols-3 gap-4 mb-6">
                    @php
                        $customer = auth()->user()->customer;
                        $ordersCount = $customer ? $customer->orders()->count() : 0;
                        $pendingOrders = $customer ? $customer->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count() : 0;
                        $addressesCount = $customer ? $customer->addresses()->count() : 0;
                        $loyaltyPoints = $customer?->loyalty_points ?? 0;
                        $pointsValue = (int) floor($loyaltyPoints / 100 * 500); // 100 pts = 500 F
                    @endphp

                    {{-- Carte Points de fidélité (pleine largeur) --}}
                    @if($loyaltyPoints > 0)
                    <div class="md:col-span-3 bg-gradient-to-r from-amber-400 to-orange-500 rounded-xl p-5 shadow-sm text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-white/20 rounded-xl">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold">{{ number_format($loyaltyPoints) }} pts</p>
                                    <p class="text-sm opacity-90">≈ {{ number_format($pointsValue, 0, ',', ' ') }} F CFA de réduction disponible</p>
                                </div>
                            </div>
                            <a href="{{ route('account.loyalty') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Voir l'historique →
                            </a>
                        </div>
                    </div>
                    @endif

                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary-500 to-primary-600"></div>
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-primary-50 rounded-xl">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ $ordersCount }}</p>
                                <p class="text-sm text-slate-500">Commandes</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-amber-500"></div>
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-amber-50 rounded-xl">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ $pendingOrders }}</p>
                                <p class="text-sm text-slate-500">En cours</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-400 to-green-500"></div>
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-green-50 rounded-xl">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">{{ $addressesCount }}</p>
                                <p class="text-sm text-slate-500">Adresses</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dernières commandes -->
                @if($customer && $customer->orders()->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h2 class="text-lg font-semibold text-slate-900">Commandes récentes</h2>
                        <a href="{{ route('account.orders') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                            Voir tout
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($customer->orders()->latest()->take(3)->get() as $order)
                        <div class="p-6 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-slate-900">#{{ $order->order_number }}</p>
                                <p class="text-sm text-slate-500">{{ $order->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-slate-900">{{ number_format($order->total, 0, ',', ' ') }} F</p>
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                                    @if($order->status === 'delivered') bg-green-100 text-green-800
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                    @elseif($order->status === 'shipped') bg-blue-100 text-blue-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ $order->status_label }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

