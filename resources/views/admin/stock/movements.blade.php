@extends('layouts.admin')

@section('title', 'Mouvements de stock')
@section('page-title', 'Historique des mouvements')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.stock.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
        <a href="{{ route('admin.stock.create-movement') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">
            Nouveau mouvement
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <select name="product_id" class="px-4 py-2 border border-slate-300 rounded-xl">
                <option value="">Tous les produits</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                @endforeach
            </select>
            <select name="type" class="px-4 py-2 border border-slate-300 rounded-xl">
                <option value="">Tous les types</option>
                <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Entrée</option>
                <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Sortie</option>
                <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Ajustement</option>
                <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>Retour</option>
            </select>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-4 py-2 border border-slate-300 rounded-xl">
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-4 py-2 border border-slate-300 rounded-xl">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">Filtrer</button>
        </form>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Produit</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Type</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Quantité</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Raison</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Utilisateur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($movements as $movement)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 text-slate-600">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $movement->product?->name ?? 'N/A' }}</p>
                            @if($movement->variant)
                                <p class="text-xs text-slate-500">Variante: {{ $movement->variant->sku }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $typeColors = ['in' => 'green', 'out' => 'red', 'adjustment' => 'purple', 'return' => 'blue', 'transfer' => 'amber'];
                                $typeLabels = ['in' => 'Entrée', 'out' => 'Sortie', 'adjustment' => 'Ajustement', 'return' => 'Retour', 'transfer' => 'Transfert'];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                @switch($movement->type)
                                    @case('in') bg-green-100 text-green-700 @break
                                    @case('out') bg-red-100 text-red-700 @break
                                    @case('adjustment') bg-purple-100 text-purple-700 @break
                                    @case('return') bg-blue-100 text-blue-700 @break
                                    @case('transfer') bg-amber-100 text-amber-700 @break
                                    @default bg-slate-100 text-slate-700
                                @endswitch">
                                {{ $typeLabels[$movement->type] ?? $movement->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold {{ $movement->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $movement->reason }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $movement->user?->name ?? 'Système' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">Aucun mouvement</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">{{ $movements->links() }}</div>
        @endif
    </div>
</div>
@endsection

