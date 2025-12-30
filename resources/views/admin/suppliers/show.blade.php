@extends('layouts.admin')

@section('title', $supplier->name)
@section('page-title', $supplier->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.suppliers.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux fournisseurs
        </a>
        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Modifier
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Infos principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations générales -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-6">Informations générales</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-slate-500">Nom</p>
                        <p class="font-medium text-slate-900">{{ $supplier->name }}</p>
                    </div>
                    @if($supplier->code)
                    <div>
                        <p class="text-sm text-slate-500">Code</p>
                        <p class="font-medium text-slate-900">{{ $supplier->code }}</p>
                    </div>
                    @endif
                    @if($supplier->email)
                    <div>
                        <p class="text-sm text-slate-500">Email</p>
                        <p class="font-medium text-slate-900">{{ $supplier->email }}</p>
                    </div>
                    @endif
                    @if($supplier->phone)
                    <div>
                        <p class="text-sm text-slate-500">Téléphone</p>
                        <p class="font-medium text-slate-900">{{ $supplier->phone }}</p>
                    </div>
                    @endif
                    @if($supplier->contact_name)
                    <div>
                        <p class="text-sm text-slate-500">Contact</p>
                        <p class="font-medium text-slate-900">{{ $supplier->contact_name }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm text-slate-500">Statut</p>
                        @if($supplier->is_active)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Actif</span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">Inactif</span>
                        @endif
                    </div>
                </div>

                @if($supplier->address || $supplier->city || $supplier->country)
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <p class="text-sm text-slate-500 mb-2">Adresse</p>
                    <p class="text-slate-900">
                        {{ $supplier->address }}<br>
                        {{ $supplier->postal_code }} {{ $supplier->city }}<br>
                        {{ $supplier->country }}
                    </p>
                </div>
                @endif
            </div>

            <!-- Historique des mouvements -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">Historique des mouvements de stock</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Produit</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Type</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Quantité</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($supplier->stockMovements as $movement)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-slate-600">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 font-medium text-slate-900">{{ $movement->product?->name ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @if($movement->type === 'in')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Entrée</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Sortie</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold {{ $movement->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $movement->type === 'in' ? '+' : '-' }}{{ $movement->quantity }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                        Aucun mouvement de stock enregistré
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Stats -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Statistiques</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Mouvements</span>
                        <span class="font-semibold text-slate-900">{{ $supplier->stockMovements->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Total entrées</span>
                        <span class="font-semibold text-green-600">+{{ $supplier->stockMovements->where('type', 'in')->sum('quantity') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600">Créé le</span>
                        <span class="text-slate-900">{{ $supplier->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($supplier->notes)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Notes</h3>
                <p class="text-slate-600 text-sm">{{ $supplier->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

