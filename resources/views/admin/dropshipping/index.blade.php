@extends('layouts.admin')

@section('title', 'Dropshipping - Commandes Fournisseurs')
@section('page-title', 'Dropshipping - Commandes Fournisseurs')

@section('content')
<div class="space-y-6">
    <!-- Stats rapides -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 border border-slate-200">
            <p class="text-sm text-slate-600">En attente</p>
            <p class="text-2xl font-bold text-amber-600">
                {{ \App\Models\OrderSupplier::where('status', 'pending')->count() }}
            </p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200">
            <p class="text-sm text-slate-600">En traitement</p>
            <p class="text-2xl font-bold text-blue-600">
                {{ \App\Models\OrderSupplier::where('status', 'processing')->count() }}
            </p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200">
            <p class="text-sm text-slate-600">Expédiées</p>
            <p class="text-2xl font-bold text-purple-600">
                {{ \App\Models\OrderSupplier::where('status', 'shipped')->count() }}
            </p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200">
            <p class="text-sm text-slate-600">Livrées</p>
            <p class="text-2xl font-bold text-green-600">
                {{ \App\Models\OrderSupplier::where('status', 'delivered')->count() }}
            </p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="N° commande..." 
                class="flex-1 min-w-[200px] px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            
            <select name="status" class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="supplier" class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Tous les fournisseurs</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                Filtrer
            </button>
            
            @if(request()->hasAny(['search', 'status', 'supplier']))
                <a href="{{ route('admin.dropshipping.index') }}" class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 font-medium rounded-xl transition-colors">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Commande</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Fournisseur</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Suivi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($orderSuppliers as $orderSupplier)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.orders.show', $orderSupplier->order) }}" class="font-mono font-medium text-blue-600 hover:text-blue-700">
                                    {{ $orderSupplier->order->order_number }}
                                </a>
                                <p class="text-xs text-slate-500">{{ $orderSupplier->order->items_count ?? 0 }} article(s)</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ $orderSupplier->supplier->name }}</p>
                                @if($orderSupplier->supplier->email)
                                    <p class="text-sm text-slate-500">{{ $orderSupplier->supplier->email }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'confirmed' => 'bg-blue-100 text-blue-700',
                                        'processing' => 'bg-indigo-100 text-indigo-700',
                                        'shipped' => 'bg-purple-100 text-purple-700',
                                        'delivered' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$orderSupplier->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $statuses[$orderSupplier->status] ?? $orderSupplier->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($orderSupplier->tracking_number)
                                    <p class="font-mono text-sm text-slate-900">{{ $orderSupplier->tracking_number }}</p>
                                    @if($orderSupplier->shipping_carrier)
                                        <p class="text-xs text-slate-500">{{ ucfirst($orderSupplier->shipping_carrier) }}</p>
                                    @endif
                                @else
                                    <span class="text-sm text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                @if($orderSupplier->total)
                                    {{ number_format($orderSupplier->total, 0, ',', ' ') }} F
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $orderSupplier->created_at->format('d/m/Y H:i') }}
                                @if($orderSupplier->shipped_at)
                                    <p class="text-xs text-purple-600">Exp: {{ $orderSupplier->shipped_at->format('d/m/Y') }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.dropshipping.show', $orderSupplier) }}" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Voir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.orders.show', $orderSupplier->order) }}" class="p-2 text-slate-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Voir commande">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                Aucune commande fournisseur trouvée
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orderSuppliers->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $orderSuppliers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

