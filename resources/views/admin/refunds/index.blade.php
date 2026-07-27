@extends('layouts.admin')

@section('title', 'Remboursements')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div>
        <h1 class="text-xl font-bold text-gray-900">Remboursements</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">Suivez et gérez les demandes de remboursement</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-[13px]">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-[13px]">{{ session('error') }}</div>
    @endif

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <select name="status" class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Tous les statuts</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>En attente</option>
                <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Traité</option>
                <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Rejeté</option>
            </select>
            <button type="submit" class="h-9 px-4 bg-gray-800 text-white font-medium text-[13px] rounded-lg hover:bg-gray-700 transition-colors">Filtrer</button>
            @if(request('status'))
                <a href="{{ route('admin.refunds.index') }}" class="h-9 px-3 inline-flex items-center text-[13px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Effacer
                </a>
            @endif
        </form>
    </div>

    {{-- Table Desktop --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">N° Remboursement</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commande</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Montant</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Motif</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($refunds as $refund)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <span class="font-mono text-[13px] font-medium text-gray-900">{{ $refund->refund_number }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.orders.show', $refund->order) }}" class="text-[13px] text-blue-600 font-medium hover:underline">
                                {{ $refund->order->order_number }}
                            </a>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-[13px] font-bold text-gray-900">{{ number_format($refund->amount, 0, ',', ' ') }} F</span>
                        </td>
                        <td class="px-5 py-4 text-[13px] text-gray-600">{{ $refund->reason_label }}</td>
                        <td class="px-5 py-4 text-center">
                            @if($refund->status === 'processed')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-50 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Traité
                                </span>
                            @elseif($refund->status === 'pending')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-50 text-amber-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> En attente
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-50 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejeté
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-[12px] text-gray-400">{{ $refund->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <p class="text-[13px] text-gray-400">Aucun remboursement</p>
                            <p class="text-[12px] text-gray-300 mt-1">Les remboursements apparaîtront ici</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($refunds->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $refunds->links() }}</div>
        @endif
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-3">
        @forelse($refunds as $refund)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="font-mono text-[13px] font-medium text-gray-900">{{ $refund->refund_number }}</span>
                @if($refund->status === 'processed')
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-700">Traité</span>
                @elseif($refund->status === 'pending')
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">En attente</span>
                @else
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-700">Rejeté</span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.orders.show', $refund->order) }}" class="text-[13px] text-blue-600 font-medium">{{ $refund->order->order_number }}</a>
                <span class="text-[13px] font-bold text-gray-900">{{ number_format($refund->amount, 0, ',', ' ') }} F</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">{{ $refund->created_at->format('d/m/Y H:i') }} · {{ $refund->reason_label }}</p>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
            <p class="text-[13px] text-gray-400">Aucun remboursement</p>
        </div>
        @endforelse
        @if($refunds->hasPages())
        <div class="mt-4">{{ $refunds->links() }}</div>
        @endif
    </div>

</div>
@endsection
