<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Remboursements</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">
                {{ $refunds->total() }} remboursement(s) au total
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <div class="flex flex-col sm:flex-row gap-3">

            {{-- Recherche --}}
            <div class="flex-1 relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Rechercher par N° remboursement ou commande..."
                    class="w-full h-9 px-3 pr-8 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                />
                <div wire:loading.delay wire:target="search" class="absolute right-2.5 top-1/2 -translate-y-1/2">
                    <svg class="w-4 h-4 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
            </div>

            {{-- Statut --}}
            <select
                wire:model.live="status"
                class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">Tous statuts</option>
                <option value="pending">En attente</option>
                <option value="approved">Approuvé</option>
                <option value="processed">Traité</option>
                <option value="rejected">Rejeté</option>
            </select>

            {{-- Reset --}}
            @if($search || $status)
                <button
                    wire:click="resetFilters"
                    class="h-9 px-3 text-[13px] text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    Réinitialiser
                </button>
            @endif
        </div>
    </div>

    {{-- Loading state --}}
    <div wire:loading.delay class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-[13px]">
        Chargement en cours...
    </div>

    {{-- Flash message --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-[13px]">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($refunds->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">N° Remboursement</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Commande</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Montant</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Raison</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Traité par</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($refunds as $refund)
                            <tr wire:key="refund-{{ $refund->id }}" class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $refund->refund_number }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($refund->order)
                                        <a href="{{ route('admin.orders.show', $refund->order) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                            {{ $refund->order->order_number }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-900">{{ number_format($refund->amount, 0, ',', ' ') }} F</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $refund->reason_label }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $refund->processedBy?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                        @if($refund->status === 'pending') bg-yellow-100 text-yellow-700
                                        @elseif($refund->status === 'approved') bg-blue-100 text-blue-700
                                        @elseif($refund->status === 'processed') bg-green-100 text-green-700
                                        @elseif($refund->status === 'rejected') bg-red-100 text-red-700
                                        @else bg-gray-100 text-gray-600
                                        @endif">
                                        {{ $refund->status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $refund->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $refunds->links() }}
            </div>
        @else
            {{-- Empty state --}}
            <div class="p-12 text-center">
                <div class="text-gray-400 mb-3">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">Aucun remboursement</h3>
                <p class="text-[13px] text-gray-500">Les remboursements apparaîtront ici.</p>
            </div>
        @endif
    </div>
</div>
