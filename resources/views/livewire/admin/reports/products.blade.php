<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 hover:text-gray-900 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Rapports
            </a>
            <h1 class="text-xl font-bold text-gray-900">Rapport produits</h1>
        </div>
        <a href="{{ route('admin.reports.products.export-csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
           class="h-9 px-4 inline-flex items-center gap-2 bg-white border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
    </div>

    {{-- Filtres réactifs --}}
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
        <div class="flex gap-1.5 pb-0.5">
            <button wire:click="$set('startDate', '{{ now()->startOfMonth()->format('Y-m-d') }}'); $set('endDate', '{{ now()->format('Y-m-d') }}')"
                class="h-9 px-3 text-[12px] font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">Ce mois</button>
            <button wire:click="$set('startDate', '{{ now()->startOfYear()->format('Y-m-d') }}'); $set('endDate', '{{ now()->format('Y-m-d') }}')"
                class="h-9 px-3 text-[12px] font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">Cette année</button>
        </div>
        <div wire:loading.delay class="self-end pb-2.5 flex items-center gap-1.5 text-[12px] text-blue-600">
            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Actualisation...
        </div>
    </div>

    {{-- Ventes par catégorie --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5" wire:loading.class="opacity-60">
        <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Ventes par catégorie</h3>
        <div class="grid md:grid-cols-2 gap-6">
            <div wire:ignore>
                <canvas id="categoryChart" height="220"></canvas>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase">Catégorie</th>
                            <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase">Qté</th>
                            <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase">CA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($categoryStats as $cat)
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $cat->name }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ $cat->quantity_sold }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ format_price($cat->revenue) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top 50 produits --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" wire:loading.class="opacity-60">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-[14px] font-semibold text-gray-900">Top 50 produits</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase w-8">#</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase">Produit</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase">SKU</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase">Catégorie</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase">Qté</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase">Cmd</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase">CA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($topProducts as $i => $product)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-gray-400 font-medium">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="font-medium text-gray-900 hover:text-blue-600">{{ $product->name }}</a>
                        </td>
                        <td class="px-4 py-3 font-mono text-[12px] text-gray-500">{{ $product->sku }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->category_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $product->quantity_sold }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ $product->orders_count }}</td>
                        <td class="px-4 py-3 text-right font-bold text-green-700">{{ format_price($product->revenue) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-[13px] text-gray-400">Aucune vente sur cette période</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Produits sans vente --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-[14px] font-semibold text-gray-900">Produits sans vente sur la période</h3>
            <p class="text-[12px] text-gray-400 mt-0.5">Ces produits n'ont généré aucune vente durant cette période</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase">Produit</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase">SKU</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase">Stock</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase">Prix</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase">Valeur stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($noSalesProducts as $product)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="font-medium text-gray-900 hover:text-blue-600">{{ $product->name }}</a>
                        </td>
                        <td class="px-4 py-3 font-mono text-[12px] text-gray-500">{{ $product->sku }}</td>
                        <td class="px-4 py-3 text-right {{ $product->stock_quantity <= 0 ? 'text-red-600 font-semibold' : 'text-gray-900' }}">{{ $product->stock_quantity }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ format_price($product->sale_price) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ format_price($product->stock_quantity * $product->sale_price) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-[13px] text-gray-400">Tous les produits ont été vendus durant cette période</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    let catChart = null;
    const COLORS = ['rgba(37,99,235,0.8)','rgba(16,185,129,0.8)','rgba(245,158,11,0.8)','rgba(239,68,68,0.8)','rgba(139,92,246,0.8)','rgba(236,72,153,0.8)','rgba(20,184,166,0.8)','rgba(251,146,60,0.8)'];

    function buildChart(data) {
        const ctx = document.getElementById('categoryChart');
        if (!ctx) return;
        if (catChart) { catChart.destroy(); catChart = null; }
        catChart = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{ data: data.revenues, backgroundColor: COLORS, borderWidth: 0 }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: c => c.label + ': ' + new Intl.NumberFormat('fr-FR').format(c.raw) + ' F CFA' } }
                }
            }
        });
    }

    buildChart(@js($chartData));

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('chart-updated', ({ data }) => buildChart(data));
    });
})();
</script>
@endpush
