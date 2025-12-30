@extends('layouts.admin')

@section('title', 'Client - ' . $customer->full_name)
@section('page-title', 'Détails du client')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.customers.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $customer->full_name }}</h1>
                <p class="text-slate-500">Client depuis {{ $customer->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                Modifier
            </a>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Infos client -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Carte profil -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl font-bold text-white">{{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}</span>
                    </div>
                    <h2 class="text-xl font-semibold text-slate-900">{{ $customer->full_name }}</h2>
                    <span class="inline-flex px-3 py-1 mt-2 rounded-full text-sm font-medium
                        @if($customer->status === 'active') bg-green-100 text-green-800
                        @elseif($customer->status === 'blocked') bg-red-100 text-red-800
                        @else bg-slate-100 text-slate-800 @endif">
                        @if($customer->status === 'active') Actif
                        @elseif($customer->status === 'blocked') Bloqué
                        @else Inactif @endif
                    </span>
                </div>

                <div class="space-y-4 text-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-slate-600">{{ $customer->email }}</span>
                    </div>
                    @if($customer->phone)
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span class="text-slate-600">{{ $customer->phone }}</span>
                    </div>
                    @endif
                    @if($customer->birthdate)
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-slate-600">{{ $customer->birthdate->format('d/m/Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Stats -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Statistiques</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Commandes</span>
                        <span class="font-semibold text-slate-900">{{ $customer->orders_count ?? $customer->orders()->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Total dépensé</span>
                        <span class="font-semibold text-slate-900">{{ format_price($customer->total_spent ?? $customer->orders()->where('payment_status', 'paid')->sum('total')) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Panier moyen</span>
                        <span class="font-semibold text-slate-900">{{ format_price($customer->average_order_value ?? ($customer->orders()->where('payment_status', 'paid')->avg('total') ?? 0)) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Dernière commande</span>
                        <span class="text-slate-600">{{ $customer->last_order_at ? $customer->last_order_at->diffForHumans() : 'Jamais' }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($customer->notes)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Notes</h3>
                <p class="text-slate-600 text-sm">{{ $customer->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Commandes et adresses -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Dernières commandes -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Dernières commandes</h3>
                    <a href="{{ route('admin.orders.index', ['customer' => $customer->id]) }}" class="text-sm text-blue-600 hover:underline">
                        Voir tout
                    </a>
                </div>
                
                @if($customer->orders->count() > 0)
                <div class="divide-y divide-slate-100">
                    @foreach($customer->orders as $order)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50">
                        <div>
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-slate-900 hover:text-blue-600">
                                #{{ $order->order_number }}
                            </a>
                            <p class="text-sm text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-slate-900">{{ format_price($order->total) }}</p>
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full
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
                @else
                <div class="p-8 text-center text-slate-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p>Aucune commande</p>
                </div>
                @endif
            </div>

            <!-- Adresses -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Adresses</h3>
                </div>
                
                @if($customer->addresses->count() > 0)
                <div class="grid md:grid-cols-2 gap-4 p-6">
                    @foreach($customer->addresses as $address)
                    <div class="p-4 border border-slate-200 rounded-xl">
                        @if($address->is_default_shipping)
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 mb-2">Par défaut</span>
                        @endif
                        <p class="font-medium text-slate-900">{{ $address->first_name }} {{ $address->last_name }}</p>
                        @if($address->company)
                            <p class="text-slate-600">{{ $address->company }}</p>
                        @endif
                        <p class="text-slate-600">{{ $address->address }}</p>
                        <p class="text-slate-600">{{ $address->postal_code }} {{ $address->city }}</p>
                        <p class="text-slate-600">{{ $address->country }}</p>
                        @if($address->phone)
                            <p class="text-slate-600 mt-2">📞 {{ $address->phone }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-8 text-center text-slate-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    <p>Aucune adresse enregistrée</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

