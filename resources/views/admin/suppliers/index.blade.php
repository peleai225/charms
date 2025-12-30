@extends('layouts.admin')

@section('title', 'Fournisseurs')
@section('page-title', 'Fournisseurs')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <p class="text-slate-600">{{ $suppliers->total() }} fournisseur(s)</p>
        <a href="{{ route('admin.suppliers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nouveau fournisseur
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Fournisseur</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Mouvements</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ $supplier->name }}</p>
                                <p class="text-sm text-slate-500">{{ $supplier->code }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                <p>{{ $supplier->email }}</p>
                                <p class="text-sm">{{ $supplier->phone }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $supplier->stock_movements_count ?? 0 }}</td>
                            <td class="px-6 py-4">
                                @if($supplier->is_active)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Actif</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">Inactif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg inline-block">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">Aucun fournisseur</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

