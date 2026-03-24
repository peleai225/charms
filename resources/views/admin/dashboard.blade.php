@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6"
     x-data="dashboardKpi()"
     x-init="init()">

    <!-- Welcome Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-600 to-indigo-600 p-6 md:p-8 shadow-lg">
        {{-- Pattern overlay --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="dash-pattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1.5" fill="white"/></pattern></defs><rect width="100%" height="100%" fill="url(#dash-pattern)"/></svg>
        </div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">
                    <span x-text="new Date().getHours() < 18 ? 'Bonjour' : 'Bonsoir'">Bonjour</span>, {{ Auth::user()->name ?? 'Admin' }} 👋
                </h1>
                <p class="mt-1 text-indigo-100 text-sm md:text-base">
                    Vous avez <span class="font-semibold text-white" x-text="kpi.pending ?? {{ $stats['pending_orders'] }}">{{ $stats['pending_orders'] }}</span> commande(s) en attente aujourd'hui
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/20 backdrop-blur-sm px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/30 transition-colors border border-white/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau produit
                </a>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Voir commandes
                </a>
            </div>
        </div>
    </div>

    <!-- Filtres période -->
    <div class="flex items-center justify-between">
        <div class="flex gap-1 bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
            <template x-for="p in periods" :key="p.value">
                <button @click="setPeriod(p.value)"
                        :class="period === p.value
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all"
                        x-text="p.label">
                </button>
            </template>
        </div>
        <div x-show="loading" class="flex items-center gap-2 text-sm text-slate-400">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Chargement...
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- CA -->
        <div class="relative bg-white rounded-2xl p-6 shadow-sm border border-slate-200 overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-blue-500 to-blue-600 rounded-l-2xl"></div>
            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-blue-50 opacity-60 group-hover:opacity-80 transition-opacity"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-500" x-text="periodLabel + ' — CA'"></p>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="kpi.revenue_fmt || '{{ format_price($stats['monthly_revenue']) }}'"></p>
                    <div class="flex items-center gap-1.5 mt-3">
                        <span :class="(kpi.growth ?? {{ $stats['revenue_growth'] }}) >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'"
                              class="text-xs font-bold px-2 py-0.5 rounded-full"
                              x-text="((kpi.growth ?? {{ $stats['revenue_growth'] }}) >= 0 ? '+' : '') + (kpi.growth ?? {{ $stats['revenue_growth'] }}) + '%'"></span>
                        <span class="text-slate-400 text-xs">vs préc.</span>
                    </div>
                </div>
                <div class="relative w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center shadow-sm">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Commandes -->
        <div class="relative bg-white rounded-2xl p-6 shadow-sm border border-slate-200 overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-orange-400 to-orange-500 rounded-l-2xl"></div>
            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-orange-50 opacity-60 group-hover:opacity-80 transition-opacity"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-orange-500">Commandes</p>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="kpi.orders ?? {{ $stats['today_orders'] }}"></p>
                    <p class="text-xs text-slate-400 mt-3"><span class="font-semibold text-orange-500" x-text="kpi.pending ?? {{ $stats['pending_orders'] }}"></span> en attente</p>
                </div>
                <div class="relative w-14 h-14 rounded-2xl bg-orange-100 flex items-center justify-center shadow-sm">
                    <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Clients -->
        <div class="relative bg-white rounded-2xl p-6 shadow-sm border border-slate-200 overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-pink-400 to-pink-500 rounded-l-2xl"></div>
            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-pink-50 opacity-60 group-hover:opacity-80 transition-opacity"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-pink-500">Total clients</p>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ number_format($stats['total_customers'], 0, ',', ' ') }}</p>
                    <p class="text-xs mt-3"><span class="font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+<span x-text="kpi.new_customers ?? {{ $stats['new_customers'] }}"></span></span> <span class="text-slate-400">sur la période</span></p>
                </div>
                <div class="relative w-14 h-14 rounded-2xl bg-pink-100 flex items-center justify-center shadow-sm">
                    <svg class="w-7 h-7 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Stock -->
        <div class="relative bg-white rounded-2xl p-6 shadow-sm border border-slate-200 overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-emerald-400 to-emerald-500 rounded-l-2xl"></div>
            <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-emerald-50 opacity-60 group-hover:opacity-80 transition-opacity"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-500">Valeur du stock</p>
                    <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ format_price($stats['stock_value']) }}</p>
                    @if($stats['out_of_stock'] > 0)
                        <p class="text-xs mt-3"><span class="font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full">{{ $stats['out_of_stock'] }} en rupture</span></p>
                    @else
                        <p class="text-xs mt-3"><span class="font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Stock OK</span></p>
                    @endif
                </div>
                <div class="relative w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center shadow-sm">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="{{ route('admin.products.create') }}" class="flex items-center gap-3 bg-white rounded-xl p-4 shadow-sm border border-slate-200 hover:border-blue-300 hover:shadow-md transition-all group">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <span class="text-sm font-semibold text-slate-700 group-hover:text-blue-700 transition-colors">Ajouter un produit</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 bg-white rounded-xl p-4 shadow-sm border border-slate-200 hover:border-orange-300 hover:shadow-md transition-all group">
            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <span class="text-sm font-semibold text-slate-700 group-hover:text-orange-700 transition-colors">Gérer les commandes</span>
        </a>
        <a href="{{ route('admin.stock.index') }}" class="flex items-center gap-3 bg-white rounded-xl p-4 shadow-sm border border-slate-200 hover:border-emerald-300 hover:shadow-md transition-all group">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <span class="text-sm font-semibold text-slate-700 group-hover:text-emerald-700 transition-colors">Stock & alertes</span>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 bg-white rounded-xl p-4 shadow-sm border border-slate-200 hover:border-purple-300 hover:shadow-md transition-all group">
            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <span class="text-sm font-semibold text-slate-700 group-hover:text-purple-700 transition-colors">Rapports</span>
        </a>
    </div>

    <!-- Graphiques et tableaux -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Graphique des ventes -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-slate-900" x-text="chartTitle"></h2>
                <div class="flex items-center gap-4 text-sm">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        Chiffre d'affaires
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        Commandes
                    </span>
                </div>
            </div>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        <!-- Top produits -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-900 mb-5">Top produits</h2>
            <div class="space-y-4">
                @forelse($topProducts as $product)
                    <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        @switch($loop->iteration)
                            @case(1)
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0 shadow-sm">1</div>
                                @break
                            @case(2)
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-300 to-slate-400 flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0 shadow-sm">2</div>
                                @break
                            @case(3)
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-300 to-orange-400 flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0 shadow-sm">3</div>
                                @break
                            @default
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-sm flex-shrink-0">{{ $loop->iteration }}</div>
                        @endswitch
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $product->name }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    @php
                                        $maxSold = $topProducts->first()->total_sold ?? 1;
                                        $pct = $maxSold > 0 ? round(($product->total_sold / $maxSold) * 100) : 0;
                                    @endphp
                                    <div class="h-full rounded-full {{ $loop->iteration === 1 ? 'bg-amber-400' : ($loop->iteration === 2 ? 'bg-slate-400' : ($loop->iteration === 3 ? 'bg-orange-400' : 'bg-blue-400')) }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-slate-500 whitespace-nowrap">{{ $product->total_sold }} vendus</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 text-center py-4">Aucune vente ce mois</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Commandes récentes et alertes stock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Commandes récentes -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Commandes récentes</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Voir tout →
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentOrders as $order)
                    <div class="p-4 flex items-center justify-between hover:bg-gradient-to-r hover:from-slate-50 hover:to-transparent transition-all group cursor-pointer">
                        <div class="flex items-center gap-3">
                            {{-- Status dot indicator --}}
                            @switch($order->status)
                                @case('delivered')
                                    <div class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0 ring-4 ring-green-100"></div>
                                    @break
                                @case('cancelled')
                                @case('refunded')
                                    <div class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0 ring-4 ring-red-100"></div>
                                    @break
                                @case('shipped')
                                    <div class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 ring-4 ring-blue-100"></div>
                                    @break
                                @case('confirmed')
                                @case('processing')
                                    <div class="w-2 h-2 rounded-full bg-cyan-500 flex-shrink-0 ring-4 ring-cyan-100"></div>
                                    @break
                                @case('pending')
                                    <div class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0 ring-4 ring-amber-100"></div>
                                    @break
                                @default
                                    <div class="w-2 h-2 rounded-full bg-slate-400 flex-shrink-0 ring-4 ring-slate-100"></div>
                            @endswitch
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center group-hover:from-blue-50 group-hover:to-indigo-100 transition-colors">
                                <span class="text-sm font-bold text-slate-600 group-hover:text-blue-700 transition-colors">
                                    {{ $order->customer ? strtoupper(substr($order->customer->first_name, 0, 1) . substr($order->customer->last_name, 0, 1)) : 'IN' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900 group-hover:text-blue-700 transition-colors">{{ $order->order_number }}</p>
                                <p class="text-xs text-slate-400">{{ $order->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="text-right flex items-center gap-3">
                            <p class="text-sm font-bold text-slate-900">{{ format_price($order->total) }}</p>
                            @switch($order->status)
                                @case('delivered')
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-100 text-green-700">{{ $order->status_label }}</span>
                                    @break
                                @case('cancelled')
                                @case('refunded')
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg bg-red-100 text-red-700">{{ $order->status_label }}</span>
                                    @break
                                @case('shipped')
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-100 text-blue-700">{{ $order->status_label }}</span>
                                    @break
                                @case('confirmed')
                                @case('processing')
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg bg-cyan-100 text-cyan-700">{{ $order->status_label }}</span>
                                    @break
                                @case('pending')
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg bg-amber-100 text-amber-700">{{ $order->status_label }}</span>
                                    @break
                                @default
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700">{{ $order->status_label }}</span>
                            @endswitch
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500">
                        Aucune commande
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Alertes stock -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Alertes stock</h2>
                <a href="{{ route('admin.stock.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Gérer →
                </a>
            </div>
            <div class="divide-y divide-slate-200">
                @forelse($lowStockProducts as $product)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                <svg class="w-5 h-5 {{ $product->stock_quantity <= 0 ? 'text-red-500' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $product->name }}</p>
                                <p class="text-xs text-slate-500">SKU: {{ $product->sku }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($product->stock_quantity <= 0)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                    Rupture
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">
                                    {{ $product->stock_quantity }} restant(s)
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500">
                        <svg class="w-12 h-12 mx-auto text-green-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Tous les stocks sont OK !
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
// Chart instance globale pour pouvoir la mettre à jour
let salesChartInstance = null;

function dashboardKpi() {
    return {
        period: 'month',
        loading: false,
        kpi: {},
        periods: [
            { value: 'today', label: "Aujourd'hui" },
            { value: 'week',  label: 'Cette semaine' },
            { value: 'month', label: 'Ce mois' },
        ],

        get periodLabel() {
            return this.periods.find(p => p.value === this.period)?.label ?? 'Ce mois';
        },

        get chartTitle() {
            const map = { today: 'Ventes aujourd\'hui (par heure)', week: 'Ventes des 7 derniers jours', month: 'Ventes des 30 derniers jours' };
            return map[this.period] ?? 'Ventes';
        },

        init() {
            // Initialiser le graphique avec les données PHP initiales
            this.$nextTick(() => {
                initChart(@json($salesChart['labels']), @json($salesChart['revenues']), @json($salesChart['orders']));
            });
        },

        async setPeriod(value) {
            if (this.period === value) return;
            this.period = value;
            await this.fetchStats();
        },

        async fetchStats() {
            this.loading = true;
            try {
                const res = await fetch('/api/admin/dashboard-stats?period=' + this.period, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                this.kpi = data;

                // Mettre à jour le graphique
                if (salesChartInstance && data.chart) {
                    salesChartInstance.data.labels = data.chart.labels;
                    salesChartInstance.data.datasets[0].data = data.chart.revenues;
                    salesChartInstance.data.datasets[1].data = data.chart.orders;
                    salesChartInstance.update('active');
                }
            } catch (e) {
                console.error('Dashboard stats error:', e);
            } finally {
                this.loading = false;
            }
        }
    };
}

function initChart(labels, revenues, orders) {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    salesChartInstance = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'CA (F CFA)',
                    data: revenues,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y',
                },
                {
                    label: 'Commandes',
                    data: orders,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false } },
            scales: {
                y:  { type: 'linear', display: true, position: 'left',  grid: { color: '#f1f5f9' } },
                y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false } },
                x:  { grid: { display: false } }
            }
        }
    });
}
</script>
@endpush
@endsection
