@extends('layouts.admin')

@section('title', 'Remboursements')
@section('page-title', 'Remboursements')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <select name="status" class="px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Traité</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeté</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-slate-800 text-white font-medium rounded-xl hover:bg-slate-700 transition-colors">Filtrer</button>
            @if(request('status'))
                <a href="{{ route('admin.refunds.index') }}" class="p-2.5 text-slate-400 hover:text-red-500 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
    </div>

    <!-- Desktop Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-50 to-slate-100/80 border-b border-slate-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">N° Remboursement</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Commande</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Motif</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($refunds as $refund)
                    <tr class="group hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono font-semibold text-slate-900">{{ $refund->refund_number }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.orders.show', $refund->order) }}" class="text-blue-600 hover:text-blue-700 font-medium hover:underline transition-colors">
                                {{ $refund->order->order_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-slate-900">{{ number_format($refund->amount, 0, ',', ' ') }} F</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $refund->reason_label }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($refund->status === 'processed')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Traité
                                </span>
                            @elseif($refund->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    En attente
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-red-50 text-red-700 ring-1 ring-red-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Rejeté
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400">{{ $refund->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-500/10 to-red-500/10 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                </div>
                                <p class="font-semibold text-slate-800 text-lg">Aucun remboursement</p>
                                <p class="text-sm text-slate-500 mt-1">Les remboursements créés apparaîtront ici.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $refunds->links() }}
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-3">
        @forelse($refunds as $refund)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-mono font-semibold text-slate-900 text-sm">{{ $refund->refund_number }}</span>
                    @if($refund->status === 'processed')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700">Traité</span>
                    @elseif($refund->status === 'pending')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-amber-50 text-amber-700">En attente</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-red-50 text-red-700">Rejeté</span>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.orders.show', $refund->order) }}" class="text-blue-600 text-sm font-medium">{{ $refund->order->order_number }}</a>
                    <span class="font-bold text-slate-900">{{ number_format($refund->amount, 0, ',', ' ') }} F</span>
                </div>
                <p class="text-xs text-slate-400 mt-2">{{ $refund->created_at->format('d/m/Y H:i') }} - {{ $refund->reason_label }}</p>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
                <p class="font-semibold text-slate-800">Aucun remboursement</p>
            </div>
        @endforelse
        <div class="mt-4">{{ $refunds->links() }}</div>
    </div>
</div>
@endsection
