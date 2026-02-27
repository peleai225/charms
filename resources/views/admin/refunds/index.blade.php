@extends('layouts.admin')

@section('title', 'Remboursements')
@section('page-title', 'Remboursements')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">{{ session('error') }}</div>
    @endif

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <select name="status" class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Traité</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeté</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl">Filtrer</button>
        </form>
    </div>

    <!-- Liste -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">N° Remboursement</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Commande</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Motif</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($refunds as $refund)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-6 py-4 font-mono font-medium text-slate-900">{{ $refund->refund_number }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.orders.show', $refund->order) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                {{ $refund->order->order_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ number_format($refund->amount, 0, ',', ' ') }} F</td>
                        <td class="px-6 py-4 text-slate-600">{{ $refund->reason_label }}</td>
                        <td class="px-6 py-4">
                            @if($refund->status === 'processed')
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Traité</span>
                            @elseif($refund->status === 'pending')
                                <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs font-medium rounded-full">En attente</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Rejeté</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $refund->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                </div>
                                <p class="font-medium text-slate-700">Aucun remboursement</p>
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
</div>
@endsection
