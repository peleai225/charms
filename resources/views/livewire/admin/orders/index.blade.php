<div id="orders-app" class="p-4 sm:p-6 space-y-5"
    x-data="ordersDrawer"
    @keydown.escape.window="if(drawerOpen) closeDrawer()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Commandes</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">
                {{ $orders->total() }} commande(s) au total
            </p>
        </div>
    </div>

    {{-- KPI Strip --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-2 lg:grid-cols-5 divide-x divide-y lg:divide-y-0 divide-gray-100">
            <div class="p-4">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">En attente</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $stats['pending'] }}</p>
            </div>
            <div class="p-4">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">En cours</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $stats['processing'] }}</p>
            </div>
            <div class="p-4">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Expédiées</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $stats['shipped'] }}</p>
            </div>
            <div class="p-4">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Aujourd'hui</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $stats['today_count'] }}</p>
            </div>
            <div class="p-4">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">CA Aujourd'hui</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($stats['today_total'], 0, ',', ' ') }} F</p>
            </div>
        </div>
    </div>

    {{-- Filters & Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

        {{-- Barre de filtres --}}
        <div class="p-4 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row gap-3">

                {{-- Recherche --}}
                <div class="flex-1 relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher par N°, email, nom..."
                        class="w-full h-9 px-3 pr-8 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                    />
                    <div wire:loading.delay wire:target="search" class="absolute right-2.5 top-1/2 -translate-y-1/2">
                        <svg class="w-4 h-4 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>
                </div>

                {{-- Statut commande --}}
                <select
                    wire:model.live="status"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="confirmed">Confirmée</option>
                    <option value="processing">En préparation</option>
                    <option value="shipped">Expédiée</option>
                    <option value="delivery_in_progress">Livreur en route</option>
                    <option value="delivered">Livrée</option>
                    <option value="cancelled">Annulée</option>
                    <option value="refunded">Remboursée</option>
                </select>

                {{-- Statut paiement --}}
                <select
                    wire:model.live="paymentStatus"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Tous paiements</option>
                    <option value="pending">En attente</option>
                    <option value="paid">Payée</option>
                    <option value="partially_paid">Partiellement payée</option>
                    <option value="failed">Échouée</option>
                    <option value="refunded">Remboursée</option>
                </select>

                {{-- Période rapide --}}
                <select
                    wire:model.live="dateRange"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Toutes dates</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                </select>

                {{-- Bouton reset --}}
                @if($search || $status || $paymentStatus || $dateRange || $dateFrom || $dateTo)
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="h-9 px-3 flex items-center justify-center border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition"
                    title="Réinitialiser les filtres">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                @endif
            </div>

            {{-- Filtres date précis (optionnels) --}}
            <div class="flex gap-3 mt-3" x-show="false">
                <input type="date" wire:model.live="dateFrom"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                    placeholder="Du"/>
                <input type="date" wire:model.live="dateTo"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                    placeholder="Au"/>
            </div>

            {{-- Indicateur de chargement global --}}
            <div wire:loading.delay wire:target="search,status,paymentStatus,dateRange,dateFrom,dateTo,resetFilters,gotoPage,nextPage,previousPage"
                class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Mise à jour des résultats...
            </div>
        </div>

        {{-- Tableau --}}
        <div class="overflow-x-auto"
            wire:loading.class.delay="opacity-60 pointer-events-none"
            wire:target="search,status,paymentStatus,dateRange,dateFrom,dateTo,resetFilters,gotoPage,nextPage,previousPage">
            <table class="w-full text-[13px]">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">N° Commande</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Client</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Date</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">Montant</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Statut</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Paiement</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Articles</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                    <tr wire:key="order-{{ $order->id }}"
                        class="hover:bg-blue-50/30 transition cursor-pointer group"
                        onclick="window.__openOrderDrawer && window.__openOrderDrawer({{ $order->id }})">

                        <td class="px-4 py-3">
                            <span class="font-semibold text-gray-900 group-hover:text-blue-600 transition">
                                {{ $order->order_number }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</p>
                            <p class="text-gray-500 text-xs">{{ $order->billing_email }}</p>
                        </td>

                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-4 py-3 text-right font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                            {{ number_format($order->total, 0, ',', ' ') }} F
                        </td>

                        <td class="px-4 py-3 text-center">
                            @php
                                $sc = [
                                    'pending'              => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'confirmed'            => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'processing'           => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'shipped'              => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'delivery_in_progress' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'delivered'            => 'bg-green-50 text-green-700 border-green-200',
                                    'cancelled'            => 'bg-red-50 text-red-700 border-red-200',
                                    'refunded'             => 'bg-gray-50 text-gray-700 border-gray-200',
                                ];
                            @endphp
                            <span data-order-status-badge="{{ $order->id }}"
                                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $sc[$order->status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                                {{ $order->status_label }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center">
                            @php
                                $pc = [
                                    'pending'         => 'bg-gray-50 text-gray-700 border-gray-200',
                                    'paid'            => 'bg-green-50 text-green-700 border-green-200',
                                    'partially_paid'  => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'failed'          => 'bg-red-50 text-red-700 border-red-200',
                                    'refunded'        => 'bg-orange-50 text-orange-700 border-orange-200',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $pc[$order->payment_status] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                                {{ $order->payment_status_label }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center text-gray-600 tabular-nums">
                            {{ $order->items_count }}
                        </td>

                        <td class="px-4 py-3 text-right" onclick="event.stopPropagation()">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="inline-flex items-center gap-1 text-gray-400 hover:text-blue-600 transition opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Aucune commande trouvée
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    {{-- QUICK-ACTION DRAWER (Alpine, inchangé) --}}
    <div wire:ignore>
        <div x-cloak x-show="drawerOpen"
             class="fixed inset-0 z-[9990] flex"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="closeDrawer()"></div>

            <div class="absolute right-0 top-0 h-full w-full max-w-[520px] bg-white shadow-2xl flex flex-col"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 @click.stop>

                {{-- Drawer Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <template x-if="drawerOrder">
                                <p class="text-[15px] font-bold text-gray-900 leading-none" x-text="drawerOrder.order_number"></p>
                            </template>
                            <template x-if="!drawerOrder && drawerLoading">
                                <div class="h-4 w-32 bg-gray-100 rounded animate-pulse"></div>
                            </template>
                            <template x-if="drawerOrder">
                                <p class="text-[12px] text-gray-400 mt-0.5" x-text="drawerOrder.created_at"></p>
                            </template>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <template x-if="drawerOrder">
                            <a :href="drawerOrder.show_url" class="h-8 px-3 flex items-center gap-1.5 border border-gray-200 text-[12px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Détail
                            </a>
                        </template>
                        <button @click="closeDrawer()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Drawer Body --}}
                <div class="flex-1 overflow-y-auto">
                    <template x-if="drawerLoading">
                        <div class="p-5 space-y-4">
                            <div class="h-4 bg-gray-100 rounded w-1/2 animate-pulse"></div>
                            <div class="h-4 bg-gray-100 rounded w-3/4 animate-pulse"></div>
                            <div class="h-24 bg-gray-100 rounded animate-pulse mt-4"></div>
                            <div class="h-24 bg-gray-100 rounded animate-pulse"></div>
                        </div>
                    </template>
                    <template x-if="drawerOrder && !drawerLoading">
                        <div class="divide-y divide-gray-100">

                            {{-- Statut --}}
                            <div class="px-5 py-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest">Statut</p>
                                    <span :class="statusClass(drawerOrder.status)"
                                          class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border"
                                          x-text="statusLabel(drawerOrder.status)"></span>
                                </div>
                                <div class="flex items-center gap-0.5">
                                    <template x-for="(s, i) in ['pending','confirmed','processing','shipped','delivered']" :key="s">
                                        <div class="flex-1 h-1.5 rounded-full transition-all duration-300"
                                             :class="['pending','confirmed','processing','shipped','delivered'].indexOf(drawerOrder.status) >= i ? (drawerOrder.status === 'delivered' ? 'bg-green-500' : 'bg-blue-600') : (drawerOrder.status === 'cancelled' ? 'bg-red-200' : 'bg-gray-100')"></div>
                                    </template>
                                </div>
                                <div class="grid grid-cols-3 gap-1.5 pt-1">
                                    <template x-for="s in ['pending','confirmed','processing','shipped','delivered','cancelled']" :key="s">
                                        <button @click="changeStatus(s)"
                                            :disabled="drawerOrder.status === s || drawerStatusSaving"
                                            :class="drawerOrder.status === s ? statusClass(s) + ' ring-2 ring-offset-1 ring-blue-400 font-bold' : 'border-gray-200 text-gray-500 hover:border-blue-300 hover:text-blue-600 hover:bg-blue-50'"
                                            class="h-8 px-2 text-[11px] font-medium border rounded-lg transition flex items-center justify-center gap-1 disabled:opacity-60 disabled:cursor-not-allowed">
                                            <svg x-show="drawerStatusSaving && drawerOrder.status !== s" class="w-3 h-3 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                            </svg>
                                            <span x-text="statusLabel(s)"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Client --}}
                            <div class="px-5 py-4">
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Client</p>
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-sm font-bold flex-shrink-0"
                                         x-text="(drawerOrder.customer_name || '??').substring(0,2).toUpperCase()"></div>
                                    <div class="flex-1 min-w-0 space-y-1">
                                        <p class="text-[13px] font-semibold text-gray-900" x-text="drawerOrder.customer_name"></p>
                                        <template x-if="drawerOrder.billing_email">
                                            <p class="text-[12px] text-gray-500 flex items-center gap-1.5">
                                                <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                <span x-text="drawerOrder.billing_email"></span>
                                            </p>
                                        </template>
                                        <template x-if="drawerOrder.billing_phone">
                                            <p class="text-[12px] text-gray-500 flex items-center gap-1.5">
                                                <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                <span x-text="drawerOrder.billing_phone"></span>
                                            </p>
                                        </template>
                                        <template x-if="drawerOrder.billing_address">
                                            <p class="text-[12px] text-gray-500 flex items-start gap-1.5">
                                                <svg class="w-3 h-3 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span x-text="drawerOrder.billing_address"></span>
                                            </p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Articles --}}
                            <div class="px-5 py-4">
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">
                                    Articles (<span x-text="drawerOrder.items.length"></span>)
                                </p>
                                <div class="space-y-3">
                                    <template x-for="item in drawerOrder.items" :key="item.id">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0 border border-gray-100">
                                                <template x-if="item.image">
                                                    <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!item.image">
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-[13px] font-medium text-gray-900 truncate" x-text="item.name"></p>
                                                <p x-show="item.variant" class="text-[11px] text-gray-400" x-text="item.variant"></p>
                                            </div>
                                            <div class="text-right flex-shrink-0">
                                                <p class="text-[13px] font-semibold text-gray-900" x-text="item.total"></p>
                                                <p class="text-[11px] text-gray-400" x-text="'x ' + item.quantity"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Totaux --}}
                            <div class="px-5 py-4 space-y-2">
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Paiement</p>
                                <div class="flex items-center justify-between text-[13px]">
                                    <span class="text-gray-500">Sous-total</span>
                                    <span class="font-medium text-gray-900" x-text="drawerOrder.subtotal_fmt"></span>
                                </div>
                                <template x-if="drawerOrder.discount_fmt">
                                    <div class="flex items-center justify-between text-[13px]">
                                        <span class="text-green-600">Réduction</span>
                                        <span class="font-medium text-green-600" x-text="drawerOrder.discount_fmt"></span>
                                    </div>
                                </template>
                                <div class="flex items-center justify-between text-[13px]">
                                    <span class="text-gray-500">Livraison</span>
                                    <span class="font-medium text-gray-900" x-text="drawerOrder.shipping_fmt"></span>
                                </div>
                                <div class="flex items-center justify-between pt-2.5 border-t border-gray-100 mt-1">
                                    <span class="text-[14px] font-bold text-gray-900">Total</span>
                                    <span class="text-[16px] font-bold text-blue-600" x-text="drawerOrder.total_fmt"></span>
                                </div>
                                <div class="flex items-center justify-between text-[12px] pt-1">
                                    <span class="text-gray-400">Paiement</span>
                                    <span class="font-medium"
                                          :class="drawerOrder.payment_status === 'paid' ? 'text-green-600' : (drawerOrder.payment_status === 'failed' ? 'text-red-600' : 'text-gray-500')"
                                          x-text="drawerOrder.payment_label"></span>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <template x-if="drawerOrder.notes">
                                <div class="px-5 py-4">
                                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-2">Note</p>
                                    <p class="text-[12px] text-gray-600 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2.5" x-text="drawerOrder.notes"></p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Drawer Footer --}}
                <div x-show="drawerOrder && !drawerLoading" class="flex-shrink-0 border-t border-gray-100 px-5 py-3 bg-gray-50/80 flex items-center gap-2">
                    <template x-if="drawerOrder">
                        <a :href="drawerOrder.invoice_url" target="_blank"
                           class="flex-1 h-9 flex items-center justify-center gap-1.5 border border-gray-200 bg-white text-[12px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Facture
                        </a>
                    </template>
                    <template x-if="drawerOrder">
                        <a :href="drawerOrder.receipt_url + '?auto_print=1'" target="_blank"
                           class="flex-1 h-9 flex items-center justify-center gap-1.5 border border-gray-200 bg-white text-[12px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Ticket
                        </a>
                    </template>
                    <template x-if="drawerOrder">
                        <a :href="drawerOrder.show_url"
                           class="flex-1 h-9 flex items-center justify-center gap-1.5 bg-blue-600 text-white text-[12px] font-semibold rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Voir commande
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('ordersDrawer', function () {
        return {
            drawerOpen: false,
            drawerLoading: false,
            drawerOrder: null,
            drawerStatusSaving: false,

            init() {
                window.__openOrderDrawer = (id) => this.openDrawer(id);
            },

            openDrawer(orderId) {
                this.drawerOpen    = true;
                this.drawerLoading = true;
                this.drawerOrder   = null;
                document.body.style.overflow = 'hidden';
                fetch('/api/admin/order-detail/' + orderId, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.json(); })
                .then((d) => { this.drawerOrder = d; this.drawerLoading = false; })
                .catch(() => { this.drawerLoading = false; });
            },

            closeDrawer() {
                this.drawerOpen = false;
                document.body.style.overflow = '';
            },

            async changeStatus(newStatus) {
                if (!this.drawerOrder || this.drawerStatusSaving) return;
                this.drawerStatusSaving = true;
                try {
                    const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                    const res  = await fetch('/admin/orders/' + this.drawerOrder.id + '/status', {
                        method: 'PATCH',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ status: newStatus })
                    });
                    const data = await res.json();
                    if (data.success || data.status) {
                        this.drawerOrder.status = newStatus;
                        const badge = document.querySelector('[data-order-status-badge="' + this.drawerOrder.id + '"]');
                        if (badge) {
                            const labels  = { pending:'En attente', confirmed:'Confirmée', processing:'En préparation', shipped:'Expédiée', delivery_in_progress:'Livreur en route', delivered:'Livrée', cancelled:'Annulée', refunded:'Remboursée' };
                            const classes = { pending:'bg-yellow-50 text-yellow-700 border-yellow-200', confirmed:'bg-blue-50 text-blue-700 border-blue-200', processing:'bg-indigo-50 text-indigo-700 border-indigo-200', shipped:'bg-purple-50 text-purple-700 border-purple-200', delivery_in_progress:'bg-orange-50 text-orange-700 border-orange-200', delivered:'bg-green-50 text-green-700 border-green-200', cancelled:'bg-red-50 text-red-700 border-red-200', refunded:'bg-gray-50 text-gray-700 border-gray-200' };
                            badge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border ' + (classes[newStatus] || 'bg-gray-50 text-gray-700 border-gray-200');
                            badge.textContent = labels[newStatus] || newStatus;
                        }
                        window.showNotification && window.showNotification('Statut mis à jour', 'success');
                    }
                } catch(e) {}
                this.drawerStatusSaving = false;
            },

            statusLabel(s) {
                const map = { pending:'En attente', confirmed:'Confirmée', processing:'En préparation', shipped:'Expédiée', delivery_in_progress:'Livreur en route', delivered:'Livrée', cancelled:'Annulée', refunded:'Remboursée' };
                return map[s] || s;
            },

            statusClass(s) {
                const map = { pending:'bg-yellow-50 text-yellow-700 border-yellow-200', confirmed:'bg-blue-50 text-blue-700 border-blue-200', processing:'bg-indigo-50 text-indigo-700 border-indigo-200', shipped:'bg-purple-50 text-purple-700 border-purple-200', delivery_in_progress:'bg-orange-50 text-orange-700 border-orange-200', delivered:'bg-green-50 text-green-700 border-green-200', cancelled:'bg-red-50 text-red-700 border-red-200', refunded:'bg-gray-50 text-gray-700 border-gray-200' };
                return map[s] || 'bg-gray-50 text-gray-700 border-gray-200';
            }
        };
    });
});
</script>
@endpush
