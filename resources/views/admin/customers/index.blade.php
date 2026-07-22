@extends('layouts.admin')

@section('title', 'Clients')

@section('content')
<div x-data="customersFilter()" class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                Clients
                <span class="ml-1.5 text-[13px] font-normal text-gray-400 tabular-nums">({{ $customers->total() }})</span>
            </h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Gérez votre base clients</p>
        </div>
    </div>

    {{-- KPI Strip --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-3 divide-x divide-gray-100">
            <div class="p-4">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Total clients</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="p-4">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Clients actifs</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $stats['active'] ?? 0 }}</p>
            </div>
            <div class="p-4">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Nouveaux ce mois</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $stats['new_this_month'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- Filters + Table card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

        {{-- Filters --}}
        <div class="p-4 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row gap-3">

                {{-- Search --}}
                <div class="relative flex-1">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        x-model="search"
                        @input.debounce.400ms="fetchResults()"
                        value="{{ request('search') }}"
                        placeholder="Rechercher par nom, email, téléphone..."
                        class="w-full h-9 pl-9 pr-8 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    />
                    <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2" x-cloak>
                        <svg class="w-3.5 h-3.5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>
                </div>

                {{-- Status --}}
                <select
                    x-model="status"
                    @change="fetchResults()"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqués</option>
                </select>

                {{-- Reset (reactive) --}}
                <template x-if="search || status">
                    <button
                        type="button"
                        @click="search = ''; status = ''; fetchResults()"
                        class="h-9 px-4 flex items-center justify-center border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition"
                    >
                        Réinitialiser
                    </button>
                </template>

            </div>
        </div>

        {{-- Table --}}
        <div :class="{ 'opacity-50 pointer-events-none': loading }" class="overflow-x-auto transition-opacity duration-150">
            <table class="w-full text-[13px]">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Client</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Téléphone</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Commandes</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">CA total</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Statut</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Inscription</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700"></th>
                    </tr>
                </thead>
                <tbody id="customers-table-body" class="divide-y divide-gray-100">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-[11px] font-semibold text-gray-600 leading-none select-none">
                                        {{ strtoupper(substr($customer->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($customer->last_name ?? '', 0, 1)) }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $customer->full_name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $customer->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $customer->phone ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-center tabular-nums text-gray-700">
                            {{ $customer->orders_count }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                            {{ format_price($customer->total_spent) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = [
                                    'active'   => 'bg-green-50 text-green-700 border-green-200',
                                    'inactive' => 'bg-gray-50 text-gray-700 border-gray-200',
                                    'blocked'  => 'bg-red-50 text-red-700 border-red-200',
                                ];
                                $statusLabels = ['active' => 'Actif', 'inactive' => 'Inactif', 'blocked' => 'Bloqué'];
                                $sc = $statusColors[$customer->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                $sl = $statusLabels[$customer->status] ?? ucfirst($customer->status);
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $sc }}">
                                {{ $sl }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $customer->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="inline-flex items-center gap-1 text-gray-600 hover:text-orange-600 transition">
                                Voir
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-[13px] text-gray-500">Aucun client trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div id="customers-pagination">
            @if($customers->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $customers->links() }}
            </div>
            @endif
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
function customersFilter() {
    return {
        search: @json(request('search', '')),
        status: @json(request('status', '')),
        loading: false,

        async fetchResults() {
            this.loading = true;

            const params = new URLSearchParams();
            if (this.search.trim()) params.set('search', this.search.trim());
            if (this.status) params.set('status', this.status);

            const base = '{{ route("admin.customers.index") }}';
            const url = base + (params.toString() ? '?' + params.toString() : '');

            window.history.replaceState({}, '', url);

            try {
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Fetch error ' + res.status);
                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');

                const newBody = doc.getElementById('customers-table-body');
                const newPagination = doc.getElementById('customers-pagination');

                if (newBody) {
                    document.getElementById('customers-table-body').innerHTML = newBody.innerHTML;
                }
                if (newPagination) {
                    document.getElementById('customers-pagination').innerHTML = newPagination.innerHTML;
                }
            } catch (e) {
                console.warn('[customersFilter] fetch failed:', e);
            }

            this.loading = false;
        }
    };
}
</script>
@endpush
