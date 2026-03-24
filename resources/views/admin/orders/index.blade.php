@extends('layouts.admin')

@section('title', 'Commandes')
@section('page-title', 'Commandes')

@section('content')
<div class="space-y-6">
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
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="N° commande, email, nom..."
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 focus:bg-white transition-colors text-sm">
            </div>

            <select name="status" class="px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 text-sm">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>En préparation</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Expédiée</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Livrée</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
            </select>

            <select name="payment_status" class="px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 text-sm">
                <option value="">Tous les paiements</option>
                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Payée</option>
                <option value="cod" {{ request('payment_status') === 'cod' ? 'selected' : '' }}>À la livraison</option>
                <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Échoué</option>
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 text-sm">

            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-slate-50 text-sm">

            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-xl transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'status', 'payment_status', 'date_from', 'date_to']))
                <a href="{{ route('admin.orders.index') }}" class="px-3 py-2.5 text-sm text-slate-500 hover:text-red-600 transition-colors" title="Réinitialiser">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-3">
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

        @if($orders->hasPages())
        <div class="mt-4">{{ $orders->links() }}</div>
        @endif
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
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
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="group hover:bg-blue-50/30 transition-colors cursor-pointer" onclick="window.location='{{ route('admin.orders.show', $order) }}'">
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
                            <td class="px-6 py-4">
                                @switch($order->status)
                                    @case('pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $order->status_label }}
                                        </span>
                                        @break
                                    @case('confirmed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            {{ $order->status_label }}
                                        </span>
                                        @break
                                    @case('processing')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            {{ $order->status_label }}
                                        </span>
                                        @break
                                    @case('shipped')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-purple-50 text-purple-600 ring-1 ring-purple-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            {{ $order->status_label }}
                                        </span>
                                        @break
                                    @case('delivered')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-50 text-green-600 ring-1 ring-green-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            {{ $order->status_label }}
                                        </span>
                                        @break
                                    @case('cancelled')
                                    @case('refunded')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-red-50 text-red-600 ring-1 ring-red-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            {{ $order->status_label }}
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-50 text-slate-600 ring-1 ring-slate-200">
                                            {{ $order->status_label }}
                                        </span>
                                @endswitch
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
                                    <a href="{{ route('admin.orders.show', $order) }}" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Voir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.orders.invoice', $order) }}" class="p-2 text-slate-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Facture PDF">
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
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
