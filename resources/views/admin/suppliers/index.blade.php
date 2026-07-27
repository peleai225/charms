@extends('layouts.admin')

@section('title', 'Fournisseurs')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Fournisseurs</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">{{ $suppliers->total() }} fournisseur(s)</p>
        </div>
        <a href="{{ route('admin.suppliers.create') }}"
            class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau fournisseur
        </a>
    </div>

    {{-- Table Desktop --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Fournisseur</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Contact</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Mouvements</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($suppliers as $supplier)
                    <tr class="group hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700 text-[11px] font-bold flex-shrink-0">
                                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-[13px] font-medium text-gray-900 group-hover:text-blue-700 transition-colors">{{ $supplier->name }}</p>
                                    <p class="text-[11px] text-gray-400 font-mono">{{ $supplier->code }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-[13px] text-gray-700">{{ $supplier->email }}</p>
                            <p class="text-[11px] text-gray-400">{{ $supplier->phone }}</p>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="text-[13px] font-medium text-gray-600">{{ $supplier->stock_movements_count ?? 0 }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($supplier->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-50 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Actif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.suppliers.edit', $supplier) }}"
                                class="h-7 px-3 inline-flex items-center gap-1.5 text-[12px] font-medium text-gray-600 hover:text-blue-700 hover:bg-blue-50 rounded transition-all opacity-0 group-hover:opacity-100">
                                Modifier
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <p class="text-[13px] text-gray-400 mb-1">Aucun fournisseur</p>
                            <p class="text-[12px] text-gray-300 mb-4">Ajoutez vos fournisseurs pour gérer vos approvisionnements</p>
                            <a href="{{ route('admin.suppliers.create') }}"
                                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors mx-auto">
                                Nouveau fournisseur
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $suppliers->links() }}</div>
        @endif
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-3">
        @forelse($suppliers as $supplier)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700 font-bold flex-shrink-0">
                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-medium text-gray-900 truncate">{{ $supplier->name }}</p>
                    <p class="text-[11px] text-gray-400 font-mono">{{ $supplier->code }}</p>
                </div>
                @if($supplier->is_active)
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-700">Actif</span>
                @else
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Inactif</span>
                @endif
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                <div class="text-[12px] text-gray-500">
                    <p>{{ $supplier->email }}</p>
                    <p>{{ $supplier->phone }}</p>
                </div>
                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="h-9 px-3 inline-flex items-center text-[13px] text-blue-600 hover:bg-blue-50 rounded transition-colors">
                    Modifier →
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
            <p class="text-[13px] text-gray-400">Aucun fournisseur</p>
        </div>
        @endforelse
        @if($suppliers->hasPages())
        <div class="mt-4">{{ $suppliers->links() }}</div>
        @endif
    </div>

</div>
@endsection
