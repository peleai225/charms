@extends('layouts.front')

@section('title', 'Mon compte')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-slate-500 mb-8">
        <a href="{{ route('home') }}" class="hover:text-primary-600">Accueil</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900">Mon compte</span>
    </nav>

    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            @include('front.account.partials.sidebar')

            <!-- Main Content -->
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-slate-900 mb-6">Tableau de bord</h1>
                
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 mb-6">
                    <p class="text-slate-600">
                        Bonjour <span class="font-semibold text-slate-900">{{ auth()->user()->name }}</span> !
                    </p>
                    <p class="text-slate-600 mt-2">
                        Depuis votre tableau de bord, vous pouvez consulter vos 
                        <a href="{{ route('account.orders') }}" class="text-primary-600 hover:underline">commandes récentes</a>,
                        gérer vos <a href="{{ route('account.addresses') }}" class="text-primary-600 hover:underline">adresses de livraison</a>,
                        et modifier vos informations de compte.
                    </p>
                </div>

                <!-- Stats rapides -->
                <div class="grid md:grid-cols-3 gap-4 mb-6">
                    @php
                        $customer = auth()->user()->customer;
                        $ordersCount = $customer ? $customer->orders()->count() : 0;
                        $pendingOrders = $customer ? $customer->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count() : 0;
                        $addressesCount = $customer ? $customer->addresses()->count() : 0;
                    @endphp
                    
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-primary-100 rounded-xl">
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
                    
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-amber-100 rounded-xl">
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
                    
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-green-100 rounded-xl">
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
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900">Commandes récentes</h2>
                        <a href="{{ route('account.orders') }}" class="text-sm text-primary-600 hover:underline">Voir tout</a>
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

