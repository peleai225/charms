@extends('layouts.admin')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Commandes</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Gérez toutes les commandes clients</p>
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
        <div class="p-4 border-b border-gray-100">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                {{-- Search --}}
                <div class="flex-1">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Rechercher par N°, email, nom..."
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    />
                </div>

                {{-- Status filter --}}
                <select name="status" class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Tous les statuts</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>En cours</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Expédiée</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Livrée</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>

                {{-- Payment status --}}
                <select name="payment_status" class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">Tous paiements</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Payée</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Échouée</option>
                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Remboursée</option>
                </select>

                <button type="submit" class="h-9 px-4 bg-gray-900 text-white text-[13px] font-semibold rounded-lg hover:bg-gray-800 transition">
                    Filtrer
                </button>

                @if(request()->hasAny(['search', 'status', 'payment_status']))
                <a href="{{ route('admin.orders.index') }}" class="h-9 px-4 flex items-center justify-center border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition">
                    Réinitialiser
                </a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
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
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-gray-900 hover:text-orange-600 transition">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <p class="font-medium text-gray-900">{{ $order->billing_first_name }} {{ $order->billing_last_name }}</p>
                                <p class="text-gray-500 text-xs">{{ $order->billing_email }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                            {{ number_format($order->total, 0, ',', ' ') }} F
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'shipped' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'delivered' => 'bg-green-50 text-green-700 border-green-200',
                                    'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                ];
                                $color = $statusColors[$order->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $color }}">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $paymentColors = [
                                    'pending' => 'bg-gray-50 text-gray-700 border-gray-200',
                                    'paid' => 'bg-green-50 text-green-700 border-green-200',
                                    'failed' => 'bg-red-50 text-red-700 border-red-200',
                                    'refunded' => 'bg-orange-50 text-orange-700 border-orange-200',
                                ];
                                $pColor = $paymentColors[$order->payment_status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $pColor }}">
                                {{ $order->payment_status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 tabular-nums">
                            {{ $order->items_count }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1 text-gray-600 hover:text-orange-600 transition">
                                Voir
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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

</div>
@endsection
