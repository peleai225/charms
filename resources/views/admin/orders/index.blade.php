@extends('layouts.admin')

@section('title', 'Commandes')
@section('page-title', 'Commandes')

@section('content')
<div class="space-y-6"
    x-data="{
        searching: false,
        filters: {
            search: '{{ request('search') }}',
            status: '{{ request('status') }}',
            payment_status: '{{ request('payment_status') }}',
            date_from: '{{ request('date_from') }}',
            date_to: '{{ request('date_to') }}'
        },
        debounceTimer: null,
        async fetchOrders() {
            this.searching = true;
            const params = new URLSearchParams();
            Object.entries(this.filters).forEach(([k, v]) => { if (v) params.append(k, v); });
            const url = '{{ route('admin.orders.index') }}?' + params.toString();
            window.history.replaceState({}, '', url);
            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newTbody = doc.querySelector('#orders-table-body');
                const currentTbody = document.querySelector('#orders-table-body');
                if (newTbody && currentTbody) currentTbody.innerHTML = newTbody.innerHTML;

                const newCards = doc.querySelector('#orders-mobile-list');
                const currentCards = document.querySelector('#orders-mobile-list');
                if (newCards && currentCards) currentCards.innerHTML = newCards.innerHTML;

                const newPag = doc.querySelector('#orders-pagination');
                const currentPag = document.querySelector('#orders-pagination');
                if (newPag && currentPag) currentPag.innerHTML = newPag.innerHTML;

                const newMobilePag = doc.querySelector('#orders-mobile-pagination');
                const currentMobilePag = document.querySelector('#orders-mobile-pagination');
                if (newMobilePag && currentMobilePag) currentMobilePag.innerHTML = newMobilePag.innerHTML;

                const newBadge = doc.querySelector('#orders-total-badge');
                const currentBadge = document.querySelector('#orders-total-badge');
                if (newBadge && currentBadge) currentBadge.textContent = newBadge.textContent;
            } catch (e) {
                console.error('Erreur lors du chargement des commandes', e);
            }
            this.searching = false;
        },
        onFilterChange() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.fetchOrders(), 400);
        },
        resetFilters() {
            this.filters = { search: '', status: '', payment_status: '', date_from: '', date_to: '' };
            this.fetchOrders();
        }
    }">

    <!-- Stats rapides -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="relative bg-white rounded-xl p-4 border border-slate-200 overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1 h-full bg-amber-400 rounded-l-xl"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">En attente</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="relative bg-white rounded-xl p-4 border border-slate-200 overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1 h-full bg-blue-400 rounded-l-xl"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">En préparation</p>
                    <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $stats['processing'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-3.937 1.968"/></svg>
                </div>
            </div>
        </div>
        <div class="relative bg-white rounded-xl p-4 border border-slate-200 overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1 h-full bg-purple-400 rounded-l-xl"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Expédiées</p>
                    <p class="text-2xl font-extrabold text-purple-600 mt-1">{{ $stats['shipped'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/></svg>
                </div>
            </div>
        </div>
        <div class="relative bg-white rounded-xl p-4 border border-slate-200 overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1 h-full bg-slate-400 rounded-l-xl"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Aujourd'hui</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['today_count'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>
        <div class="relative bg-white rounded-xl p-4 border border-slate-200 overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 left-0 w-1 h-full bg-green-400 rounded-l-xl"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">CA Aujourd'hui</p>
                    <p class="text-2xl font-extrabold text-green-600 mt-1">{{ number_format($stats['today_total'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" @submit.prevent="fetchOrders()" class="flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input
                    type="text"
                    name="search"
                    x-model="filters.search"
                    @input="onFilterChange()"
                    placeholder="N° commande, email, nom..."
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 focus:bg-white transition-colors text-sm">
            </div>

            <div class="relative">
                <select
                    name="status"
                    x-model="filters.status"
                    @change="onFilterChange()"
                    class="px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 text-sm pr-8">
                    <option value="">Tous les statuts <span id="orders-total-badge" class="font-semibold">({{ $orders->total() }})</span></option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>En préparation</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Expédiée</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Livrée</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>

            <select
                name="payment_status"
                x-model="filters.payment_status"
                @change="onFilterChange()"
                class="px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 text-sm">
                <option value="">Tous les paiements</option>
                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Payée</option>
                <option value="cod" {{ request('payment_status') === 'cod' ? 'selected' : '' }}>À la livraison</option>
                <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Échoué</option>
            </select>

            <input
                type="date"
                name="date_from"
                x-model="filters.date_from"
                @change="onFilterChange()"
                value="{{ request('date_from') }}"
                class="px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 text-sm">

            <input
                type="date"
                name="date_to"
                x-model="filters.date_to"
                @change="onFilterChange()"
                value="{{ request('date_to') }}"
                class="px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 text-sm">

            <!-- Loading spinner / Filtrer button -->
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-xl transition-colors text-sm">
                <span x-show="!searching">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </span>
                <span x-show="searching">
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </span>
                <span x-text="searching ? 'Chargement...' : 'Filtrer'">Filtrer</span>
            </button>

            <button
                type="button"
                x-show="filters.search || filters.status || filters.payment_status || filters.date_from || filters.date_to"
                @click="resetFilters()"
                class="px-3 py-2.5 text-sm text-slate-500 hover:text-red-600 transition-colors"
                title="Réinitialiser">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            @if(request()->hasAny(['search', 'status', 'payment_status', 'date_from', 'date_to']))
                {{-- Fallback no-JS reset link (hidden when Alpine is active) --}}
                <a x-cloak href="{{ route('admin.orders.index') }}" class="px-3 py-2.5 text-sm text-slate-500 hover:text-red-600 transition-colors" title="Réinitialiser">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>

        <!-- Loading overlay indicator -->
        <div x-show="searching" x-transition.opacity class="mt-2 flex items-center gap-2 text-xs text-slate-500">
            <svg class="w-3.5 h-3.5 animate-spin text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Mise à jour des résultats...
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden">
        <div id="orders-mobile-list" class="space-y-3">
            @forelse($orders as $order)
            <a href="{{ route('admin.orders.show', $order) }}" class="block bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-shadow active:scale-[0.99]">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-mono font-semibold text-blue-600 text-sm">{{ $order->order_number }}</span>
                    @switch($order->status)
                        @case('pending')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-50 text-amber-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>{{ $order->status_label }}
                            </span>@break
                        @case('confirmed')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-50 text-blue-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>{{ $order->status_label }}
                            </span>@break
                        @case('processing')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>{{ $order->status_label }}
                            </span>@break
                        @case('shipped')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-50 text-purple-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>{{ $order->status_label }}
                            </span>@break
                        @case('delivered')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-green-50 text-green-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>{{ $order->status_label }}
                            </span>@break
                        @case('cancelled')
                        @case('refunded')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-red-50 text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>{{ $order->status_label }}
                            </span>@break
                        @default
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-50 text-slate-600">{{ $order->status_label }}</span>
                    @endswitch
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-slate-900 text-sm">{{ $order->billing_full_name }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $order->items_count }} article(s) · {{ $order->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-slate-900">{{ number_format($order->total, 0, ',', ' ') }} F</p>
                        @if($order->payment_status === 'paid')
                            <span class="text-xs text-green-600 font-medium">Payée</span>
                        @elseif($order->payment_status === 'cod')
                            <span class="text-xs text-blue-600 font-medium">À la livraison</span>
                        @else
                            <span class="text-xs text-amber-600 font-medium">En attente</span>
                        @endif
                    </div>
                </div>
            </a>
            @empty
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <p class="font-medium text-slate-700">Aucune commande</p>
            </div>
            @endforelse
        </div>

        @if($orders->hasPages())
        <div id="orders-mobile-pagination" class="mt-4">{{ $orders->links() }}</div>
        @else
        <div id="orders-mobile-pagination"></div>
        @endif
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto" x-bind:class="searching ? 'opacity-60 pointer-events-none' : ''">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-50 to-slate-100/50 border-b border-slate-200">
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Commande</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Paiement</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body" class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="group hover:bg-blue-50/30 transition-colors cursor-pointer" @click="$dispatch('open-order-drawer', { id: {{ $order->id }} })">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-medium text-blue-600 hover:text-blue-700">
                                    {{ $order->order_number }}
                                </a>
                                <p class="text-xs text-slate-500">{{ $order->items_count }} article(s)</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ $order->billing_full_name }}</p>
                                <p class="text-sm text-slate-500">{{ $order->billing_email }}</p>
                            </td>
                            <td class="px-6 py-4" onclick="event.stopPropagation()">
                                <div x-data="{ changing: false, currentStatus: '{{ $order->status }}' }">
                                    <!-- Status badge, shown when not editing -->
                                    <div x-show="!changing">
                                        @switch($order->status)
                                            @case('pending')
                                                <span x-show="currentStatus === 'pending'" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>{{ $order->status_label }}
                                                </span>
                                                @break
                                            @case('confirmed')
                                                <span x-show="currentStatus === 'confirmed'" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>{{ $order->status_label }}
                                                </span>
                                                @break
                                            @case('processing')
                                                <span x-show="currentStatus === 'processing'" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>{{ $order->status_label }}
                                                </span>
                                                @break
                                            @case('shipped')
                                                <span x-show="currentStatus === 'shipped'" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-purple-50 text-purple-600 ring-1 ring-purple-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>{{ $order->status_label }}
                                                </span>
                                                @break
                                            @case('delivered')
                                                <span x-show="currentStatus === 'delivered'" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-50 text-green-600 ring-1 ring-green-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>{{ $order->status_label }}
                                                </span>
                                                @break
                                            @case('cancelled')
                                            @case('refunded')
                                                <span x-show="currentStatus === 'cancelled' || currentStatus === 'refunded'" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-red-50 text-red-600 ring-1 ring-red-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>{{ $order->status_label }}
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-50 text-slate-600 ring-1 ring-slate-200">
                                                    {{ $order->status_label }}
                                                </span>
                                        @endswitch

                                        {{-- Dynamic badge shown after AJAX status update --}}
                                        <template x-if="currentStatus !== '{{ $order->status }}'">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-50 text-slate-700 ring-1 ring-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                <span x-text="{
                                                    pending: 'En attente',
                                                    confirmed: 'Confirmée',
                                                    processing: 'En préparation',
                                                    shipped: 'Expédiée',
                                                    delivered: 'Livrée',
                                                    cancelled: 'Annulée',
                                                    refunded: 'Remboursée'
                                                }[currentStatus] || currentStatus"></span>
                                            </span>
                                        </template>

                                        <button
                                            @click="changing = true"
                                            class="text-[10px] text-slate-400 hover:text-blue-600 mt-0.5 block transition-colors leading-tight"
                                            title="Modifier le statut">
                                            modifier
                                        </button>
                                    </div>

                                    <!-- Inline status select, shown when editing -->
                                    <div x-show="changing" x-transition>
                                        <select
                                            x-init="$el.value = currentStatus"
                                            @change.stop="
                                                const newStatus = $event.target.value;
                                                fetch('{{ route('admin.orders.status', $order) }}', {
                                                    method: 'PATCH',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                        'Accept': 'application/json'
                                                    },
                                                    body: JSON.stringify({ status: newStatus })
                                                })
                                                .then(r => r.json())
                                                .then(d => {
                                                    if (d.success !== false) {
                                                        currentStatus = newStatus;
                                                        changing = false;
                                                    } else {
                                                        alert(d.message || 'Erreur lors de la mise à jour');
                                                        changing = false;
                                                    }
                                                })
                                                .catch(() => {
                                                    alert('Erreur réseau, veuillez réessayer.');
                                                    changing = false;
                                                });
                                            "
                                            class="text-xs border border-blue-300 rounded-lg px-2 py-1.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 w-full max-w-[140px]">
                                            <option value="pending">En attente</option>
                                            <option value="confirmed">Confirmée</option>
                                            <option value="processing">En préparation</option>
                                            <option value="shipped">Expédiée</option>
                                            <option value="delivered">Livrée</option>
                                            <option value="cancelled">Annulée</option>
                                        </select>
                                        <button
                                            @click="changing = false"
                                            class="text-[10px] text-slate-400 hover:text-red-500 mt-0.5 block transition-colors leading-tight">
                                            annuler
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($order->payment_status === 'paid')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Payée</span>
                                @elseif($order->payment_status === 'cod')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">À la livraison</span>
                                @elseif($order->payment_status === 'failed')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Échoué</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">En attente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ number_format($order->total, 0, ',', ' ') }} F
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Voir" onclick="event.stopPropagation()">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.orders.invoice', $order) }}" class="p-2 text-slate-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Facture PDF" onclick="event.stopPropagation()">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <p class="font-medium text-slate-700">Aucune commande trouvée</p>
                                    <p class="text-sm text-slate-500 mt-1">Les commandes apparaîtront ici lorsqu'elles seront passées.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div id="orders-pagination" class="px-6 py-4 border-t border-slate-200">
                {{ $orders->links() }}
            </div>
        @else
            <div id="orders-pagination"></div>
        @endif
    </div>
</div>

{{-- ====== SLIDE-OVER QUICK VIEW DRAWER ====== --}}
<div x-data="orderDrawer()"
     x-ref="drawer"
     x-show="open"
     x-cloak
     x-init="$watch('open', v => { document.body.style.overflow = v ? 'hidden' : '' })"
     @open-order-drawer.window="openOrder($event.detail.id)"
     class="fixed inset-0 z-50 overflow-hidden"
     @keydown.escape.window="close()">

    {{-- Backdrop --}}
    <div x-show="open"
         @click="close()"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm">
    </div>

    {{-- Panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-full"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-full"
         class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-slate-900 font-mono" x-text="order?.order_number || '...'"></h2>
                    <p class="text-xs text-slate-500" x-text="order?.created_at || ''"></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <template x-if="order">
                    <a :href="order.show_url" class="text-xs text-blue-600 hover:text-blue-700 font-medium px-3 py-1.5 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        Voir détails
                    </a>
                </template>
                <button @click="close()" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Loading state --}}
        <div x-show="loading" class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <svg class="w-8 h-8 animate-spin text-blue-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <p class="text-sm text-slate-400">Chargement...</p>
            </div>
        </div>

        {{-- Content --}}
        <div x-show="!loading && order" class="flex-1 overflow-y-auto">

            {{-- Status banner --}}
            <div class="px-6 py-3 flex items-center justify-between"
                 :class="{
                    'bg-amber-50 border-b border-amber-100': order?.status === 'pending',
                    'bg-blue-50 border-b border-blue-100': order?.status === 'confirmed',
                    'bg-indigo-50 border-b border-indigo-100': order?.status === 'processing',
                    'bg-purple-50 border-b border-purple-100': order?.status === 'shipped',
                    'bg-green-50 border-b border-green-100': order?.status === 'delivered',
                    'bg-red-50 border-b border-red-100': order?.status === 'cancelled'
                 }">
                <span class="text-sm font-semibold" :class="{
                    'text-amber-700': order?.status === 'pending',
                    'text-blue-700': order?.status === 'confirmed',
                    'text-indigo-700': order?.status === 'processing',
                    'text-purple-700': order?.status === 'shipped',
                    'text-green-700': order?.status === 'delivered',
                    'text-red-700': order?.status === 'cancelled'
                }" x-text="statusLabel(order?.status)"></span>
                <span class="text-sm font-bold text-slate-900" x-text="order?.total_fmt"></span>
            </div>

            <div class="p-6 space-y-5">

                {{-- Customer info --}}
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Client</p>
                    <p class="font-semibold text-slate-900" x-text="order?.customer_name"></p>
                    <template x-if="order?.billing_email">
                        <a :href="'mailto:' + order.billing_email" class="flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 mt-1 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span x-text="order.billing_email"></span>
                        </a>
                    </template>
                    <template x-if="order?.billing_phone">
                        <a :href="'tel:' + order.billing_phone" class="flex items-center gap-1.5 text-xs text-slate-600 hover:text-slate-900 mt-1 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span x-text="order.billing_phone"></span>
                        </a>
                    </template>
                </div>

                {{-- Items --}}
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Articles</p>
                    <div class="space-y-2">
                        <template x-for="item in (order?.items || [])" :key="item.id">
                            <div class="flex items-center gap-3 bg-white border border-slate-100 rounded-xl p-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 flex-shrink-0 overflow-hidden">
                                    <template x-if="item.image">
                                        <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.image">
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900 truncate" x-text="item.name"></p>
                                    <p class="text-xs text-slate-400" x-text="'×' + item.quantity + (item.variant ? ' · ' + item.variant : '')"></p>
                                </div>
                                <span class="text-sm font-bold text-slate-900 flex-shrink-0" x-text="item.total"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Totals --}}
                <div class="bg-slate-50 rounded-xl p-4 space-y-1.5">
                    <template x-if="order?.discount_amount && order.discount_amount > 0">
                        <div class="flex justify-between text-sm text-green-600">
                            <span>Réduction</span>
                            <span class="font-medium" x-text="'-' + order.discount_fmt"></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>Livraison</span>
                        <span x-text="order?.shipping_fmt || 'Gratuite'"></span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-900 pt-1 border-t border-slate-200">
                        <span>Total</span>
                        <span class="text-lg" x-text="order?.total_fmt"></span>
                    </div>
                </div>

                {{-- Quick status update --}}
                <div x-data="{ statusSaving: false, statusSaved: false, newStatus: order?.status }">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Changer le statut</p>
                    <div class="flex gap-2">
                        <select x-model="newStatus"
                            class="flex-1 px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white">
                            <option value="pending">En attente</option>
                            <option value="confirmed">Confirmée</option>
                            <option value="processing">En préparation</option>
                            <option value="shipped">Expédiée</option>
                            <option value="delivered">Livrée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                        <button
                            @click="
                                if(statusSaving || !order) return;
                                statusSaving = true;
                                fetch('/admin/orders/' + order.id + '/status', {
                                    method: 'PATCH',
                                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json' },
                                    body: JSON.stringify({ status: newStatus })
                                }).then(r => r.json()).then(d => {
                                    statusSaving = false;
                                    statusSaved = true;
                                    order.status = newStatus;
                                    if(window.Alpine?.store('notify')) window.Alpine.store('notify').success('Statut mis à jour');
                                    setTimeout(() => statusSaved = false, 2000);
                                }).catch(() => statusSaving = false);
                            "
                            :disabled="statusSaving"
                            :class="statusSaved ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'"
                            class="px-4 py-2 text-white font-medium rounded-xl transition-all text-sm flex items-center gap-2">
                            <template x-if="statusSaving">
                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </template>
                            <template x-if="!statusSaving && statusSaved">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="!statusSaving && !statusSaved">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer actions --}}
        <div class="px-6 py-4 border-t border-slate-200 bg-white flex-shrink-0 flex gap-3" x-show="!loading && order">
            <template x-if="order">
                <a :href="order.invoice_url" target="_blank"
                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors text-sm shadow-sm shadow-green-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimer facture
                </a>
            </template>
            <template x-if="order">
                <a :href="order.show_url"
                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Voir détails
                </a>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function orderDrawer() {
    return {
        open: false,
        loading: false,
        order: null,

        async openOrder(orderId) {
            this.open = true;
            this.loading = true;
            this.order = null;
            try {
                const r = await fetch('/api/admin/order-detail/' + orderId, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (r.ok) {
                    this.order = await r.json();
                }
            } catch(e) {}
            this.loading = false;
        },

        close() {
            this.open = false;
            document.body.style.overflow = '';
        },

        statusLabel(status) {
            const labels = {
                pending: 'En attente',
                confirmed: 'Confirmée',
                processing: 'En préparation',
                shipped: 'Expédiée',
                delivered: 'Livrée',
                cancelled: 'Annulée'
            };
            return labels[status] || status;
        }
    };
}
</script>
@endpush

@endsection
