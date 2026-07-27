<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 hover:text-gray-900 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Rapports
            </a>
            <h1 class="text-xl font-bold text-gray-900">Rapport des ventes</h1>
        </div>
        {{-- Exports — liens classiques avec les filtres actuels dans l'URL --}}
        <div class="flex gap-2">
            <a href="{{ route('admin.reports.sales.export-csv', ['start_date' => $startDate, 'end_date' => $endDate, 'group_by' => $groupBy]) }}"
               class="h-9 px-4 inline-flex items-center gap-2 bg-white border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
            <a href="{{ route('admin.reports.sales.export-pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'group_by' => $groupBy]) }}"
               class="h-9 px-4 inline-flex items-center gap-2 bg-white border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </a>
        </div>
    </div>

    {{-- Filtres réactifs (Livewire — zéro rechargement) --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-[11px] font-medium text-gray-500 mb-1">Date début</label>
            <input type="date" wire:model.live="startDate"
                class="h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-[11px] font-medium text-gray-500 mb-1">Date fin</label>
            <input type="date" wire:model.live="endDate"
                class="h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-[11px] font-medium text-gray-500 mb-1">Grouper par</label>
            <select wire:model.live="groupBy"
                class="h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="day">Par jour</option>
                <option value="week">Par semaine</option>
                <option value="month">Par mois</option>
            </select>
        </div>
        {{-- Raccourcis période --}}
        <div class="flex gap-1.5 pb-0.5">
            <button wire:click="$set('startDate', '{{ now()->startOfMonth()->format('Y-m-d') }}'); $set('endDate', '{{ now()->format('Y-m-d') }}')"
                class="h-9 px-3 text-[12px] font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">Ce mois</button>
            <button wire:click="$set('startDate', '{{ now()->startOfWeek()->format('Y-m-d') }}'); $set('endDate', '{{ now()->format('Y-m-d') }}')"
                class="h-9 px-3 text-[12px] font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">Cette semaine</button>
            <button wire:click="$set('startDate', '{{ now()->startOfYear()->format('Y-m-d') }}'); $set('endDate', '{{ now()->format('Y-m-d') }}')"
                class="h-9 px-3 text-[12px] font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">Cette année</button>
        </div>
        <div wire:loading.delay class="self-end pb-2.5 flex items-center gap-1.5 text-[12px] text-blue-600">
            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Actualisation...
        </div>
    </div>

    {{-- KPI strip --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" wire:loading.class="opacity-60">
        <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-gray-100">
            <div class="p-5">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-[12px] text-gray-500">Chiffre d'affaires</p>
                    @if($comparison['revenue']['direction'] === 'up')
                        <span class="text-[11px] font-semibold text-green-700 bg-green-50 px-1.5 py-0.5 rounded-full">+{{ $comparison['revenue']['value'] }}%</span>
                    @elseif($comparison['revenue']['direction'] === 'down')
                        <span class="text-[11px] font-semibold text-red-700 bg-red-50 px-1.5 py-0.5 rounded-full">-{{ $comparison['revenue']['value'] }}%</span>
                    @endif
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ format_price($totals['revenue']) }}</p>
            </div>
            <div class="p-5">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-[12px] text-gray-500">Commandes</p>
                    @if($comparison['orders']['direction'] === 'up')
                        <span class="text-[11px] font-semibold text-green-700 bg-green-50 px-1.5 py-0.5 rounded-full">+{{ $comparison['orders']['value'] }}%</span>
                    @elseif($comparison['orders']['direction'] === 'down')
                        <span class="text-[11px] font-semibold text-red-700 bg-red-50 px-1.5 py-0.5 rounded-full">-{{ $comparison['orders']['value'] }}%</span>
                    @endif
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $totals['orders'] }}</p>
            </div>
            <div class="p-5">
                <p class="text-[12px] text-gray-500 mb-1">Panier moyen</p>
                <p class="text-2xl font-bold text-gray-900">{{ format_price($totals['average']) }}</p>
            </div>
            <div class="p-5">
                <p class="text-[12px] text-gray-500 mb-1">Réductions</p>
                <p class="text-2xl font-bold text-red-600">{{ format_price($totals['discounts']) }}</p>
            </div>
        </div>
    </div>

    {{-- Graphique Chart.js — mis à jour via $wire.on('chart-updated') --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Évolution des ventes</h3>
        <div wire:ignore>
            <canvas id="salesChart" height="90"></canvas>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" wire:loading.class="opacity-60">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-[14px] font-semibold text-gray-900">Détail par période</h3>
            <span class="text-[12px] text-gray-400">{{ $salesData->count() }} ligne(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Période</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commandes</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">CA</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Panier moy.</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Réductions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($salesData as $row)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5 font-medium text-[13px] text-gray-900">{{ $row->period }}</td>
                        <td class="px-5 py-3.5 text-right text-[13px] text-gray-600">{{ $row->orders_count }}</td>
                        <td class="px-5 py-3.5 text-right text-[13px] font-semibold text-gray-900">{{ format_price($row->revenue) }}</td>
                        <td class="px-5 py-3.5 text-right text-[13px] text-gray-600">{{ format_price($row->average_order) }}</td>
                        <td class="px-5 py-3.5 text-right text-[13px] text-red-600">{{ format_price($row->discounts) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-[13px] text-gray-400">Aucune vente sur cette période</td></tr>
                    @endforelse
                </tbody>
                @if($salesData->count() > 0)
                <tfoot>
                    <tr class="bg-gray-50 border-t-2 border-gray-200">
                        <td class="px-5 py-3.5 text-[13px] font-bold text-gray-900">TOTAL</td>
                        <td class="px-5 py-3.5 text-right text-[13px] font-bold text-gray-900">{{ $totals['orders'] }}</td>
                        <td class="px-5 py-3.5 text-right text-[13px] font-bold text-gray-900">{{ format_price($totals['revenue']) }}</td>
                        <td class="px-5 py-3.5 text-right text-[13px] font-bold text-gray-900">{{ format_price($totals['average']) }}</td>
                        <td class="px-5 py-3.5 text-right text-[13px] font-bold text-red-600">{{ format_price($totals['discounts']) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    let salesChart = null;

    const COLORS = {
        bar:  'rgba(37, 99, 235, 0.8)',
        line: 'rgb(16, 185, 129)',
    };

    function buildChart(data) {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;
        if (salesChart) { salesChart.destroy(); salesChart = null; }
        salesChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'CA (F CFA)',
                    data: data.revenues,
                    backgroundColor: COLORS.bar,
                    borderRadius: 6,
                    yAxisID: 'y',
                }, {
                    label: 'Commandes',
                    data: data.orderCounts,
                    type: 'line',
                    borderColor: COLORS.line,
                    backgroundColor: 'transparent',
                    yAxisID: 'y1',
                    tension: 0.4,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y:  { beginAtZero: true, position: 'left',  ticks: { callback: v => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v) } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
                }
            }
        });
    }

    // Init au chargement
    buildChart(@js($chartData));

    // Mise à jour Livewire sans rechargement de page
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('chart-updated', ({ chart }) => buildChart(chart));
    });
})();
</script>
@endpush
