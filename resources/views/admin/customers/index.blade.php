@extends('layouts.admin')

@section('title', 'Clients')
@section('page-title', 'Clients')

@section('content')
<div class="space-y-6">
    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Total clients</p>
                <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Clients actifs</p>
                <p class="text-2xl font-bold text-slate-900">{{ $stats['active'] ?? 0 }}</p>
            </div>
            <div class="p-3 bg-green-100 rounded-full text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Nouveaux ce mois</p>
                <p class="text-2xl font-bold text-slate-900">{{ $stats['new_this_month'] ?? 0 }}</p>
            </div>
            <div class="p-3 bg-purple-100 rounded-full text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, email, téléphone..." 
                class="flex-1 min-w-[200px] px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            <select name="status" class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqués</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                Rechercher
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Commandes</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Total dépensé</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $customer->full_name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $customer->email }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $customer->orders_count }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ format_price($customer->total_spent) }}</td>
                            <td class="px-6 py-4">
                                @if($customer->status === 'active')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Actif</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">{{ ucfirst($customer->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg inline-block">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">Aucun client</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

