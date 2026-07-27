@extends('layouts.admin')

@section('title', 'Dropshipping')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div>
        <h1 class="text-xl font-bold text-gray-900">Dropshipping — Commandes fournisseurs</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">Suivi des commandes transmises aux fournisseurs</p>
    </div>

    {{-- KPI strip --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-gray-100">
            <div class="p-4">
                <p class="text-[12px] text-gray-500">En attente</p>
                <p class="text-2xl font-bold text-amber-600 mt-0.5">
                    {{ \App\Models\OrderSupplier::where('status', 'pending')->count() }}
                </p>
            </div>
            <div class="p-4">
                <p class="text-[12px] text-gray-500">En traitement</p>
                <p class="text-2xl font-bold text-blue-600 mt-0.5">
                    {{ \App\Models\OrderSupplier::where('status', 'processing')->count() }}
                </p>
            </div>
            <div class="p-4">
                <p class="text-[12px] text-gray-500">Expédiées</p>
                <p class="text-2xl font-bold text-purple-600 mt-0.5">
                    {{ \App\Models\OrderSupplier::where('status', 'shipped')->count() }}
                </p>
            </div>
            <div class="p-4">
                <p class="text-[12px] text-gray-500">Livrées</p>
                <p class="text-2xl font-bold text-green-600 mt-0.5">
                    {{ \App\Models\OrderSupplier::where('status', 'delivered')->count() }}
                </p>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="N° commande..."
                class="flex-1 min-w-[180px] h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

            <select name="status" class="h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="supplier" class="h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Tous les fournisseurs</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="h-9 px-4 bg-gray-800 text-white font-medium text-[13px] rounded-lg hover:bg-gray-700 transition-colors">Filtrer</button>

            @if(request()->hasAny(['search', 'status', 'supplier']))
                <a href="{{ route('admin.dropshipping.index') }}" class="h-9 px-3 inline-flex items-center text-[13px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Effacer
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commande</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Fournisseur</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Suivi</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Montant</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orderSuppliers as $orderSupplier)
                    <tr class="group hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.orders.show', $orderSupplier->order) }}" class="font-mono text-[13px] font-medium text-blue-600 hover:underline">
                                {{ $orderSupplier->order->order_number }}
                            </a>
                            <p class="text-[11px] text-gray-400">{{ $orderSupplier->order->items_count ?? 0 }} article(s)</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-[13px] font-medium text-gray-900">{{ $orderSupplier->supplier->name }}</p>
                            @if($orderSupplier->supplier->email)
                                <p class="text-[11px] text-gray-400">{{ $orderSupplier->supplier->email }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $statusClasses = [
                                    'pending'    => 'bg-amber-50 text-amber-700',
                                    'confirmed'  => 'bg-blue-50 text-blue-700',
                                    'processing' => 'bg-blue-50 text-blue-700',
                                    'shipped'    => 'bg-purple-50 text-purple-700',
                                    'delivered'  => 'bg-green-50 text-green-700',
                                    'cancelled'  => 'bg-red-50 text-red-700',
                                ];
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $statusClasses[$orderSupplier->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statuses[$orderSupplier->status] ?? $orderSupplier->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if($orderSupplier->tracking_number)
                                <p class="font-mono text-[12px] text-gray-900">{{ $orderSupplier->tracking_number }}</p>
                                @if($orderSupplier->shipping_carrier)
                                    <p class="text-[11px] text-gray-400">{{ ucfirst($orderSupplier->shipping_carrier) }}</p>
                                @endif
                            @else
                                <span class="text-[13px] text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($orderSupplier->total)
                                <span class="text-[13px] font-bold text-gray-900">{{ number_format($orderSupplier->total, 0, ',', ' ') }} F</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-[12px] text-gray-500">
                            {{ $orderSupplier->created_at->format('d/m/Y H:i') }}
                            @if($orderSupplier->shipped_at)
                                <p class="text-[11px] text-purple-600">Exp: {{ $orderSupplier->shipped_at->format('d/m/Y') }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('admin.dropshipping.show', $orderSupplier) }}"
                                   class="h-7 w-7 inline-flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-all" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.orders.show', $orderSupplier->order) }}"
                                   class="h-7 w-7 inline-flex items-center justify-center text-gray-500 hover:text-green-600 hover:bg-green-50 rounded transition-all" title="Voir commande">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <p class="text-[13px] text-gray-400">Aucune commande fournisseur trouvée</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orderSuppliers->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $orderSuppliers->links() }}</div>
        @endif
    </div>
</div>
@endsection
