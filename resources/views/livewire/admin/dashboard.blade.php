<div class="space-y-5">

    {{-- ── KPI STRIP ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
         wire:loading.class="opacity-50">
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-100">

            {{-- CA --}}
            <div class="p-6">
                <p class="text-[12px] text-gray-500 font-medium">Chiffre d'affaires</p>
                <p class="text-[28px] font-bold text-gray-900 mt-1 leading-none">
                    {{ format_price($stats['monthly_revenue']) }}
                </p>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold px-1.5 py-0.5 rounded
                                 {{ $stats['revenue_growth'] >= 0 ? 'text-green-600 bg-green-50' : 'text-red-500 bg-red-50' }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($stats['revenue_growth'] >= 0)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7 7 7"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7-7-7"/>
                            @endif
                        </svg>
                        {{ abs($stats['revenue_growth']) }}%
                    </span>
                    <span class="text-[11px] text-gray-400">/Month</span>
                </div>
            </div>

            {{-- Commandes --}}
            <div class="p-6">
                <p class="text-[12px] text-gray-500 font-medium">Total Commandes</p>
                <p class="text-[28px] font-bold text-gray-900 mt-1 leading-none">{{ $stats['today_orders'] }}</p>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold px-1.5 py-0.5 rounded bg-amber-50 text-amber-600">
                        {{ $stats['pending_orders'] }} en attente
                    </span>
                </div>
            </div>

            {{-- Clients --}}
            <div class="p-6">
                <p class="text-[12px] text-gray-500 font-medium">Nouveaux Clients</p>
                <p class="text-[28px] font-bold text-gray-900 mt-1 leading-none">{{ $stats['new_customers'] }}</p>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold px-1.5 py-0.5 rounded
                                 {{ $stats['new_customers'] >= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500' }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7 7 7"/>
                        </svg>
                        {{ $stats['new_customers'] }}
                    </span>
                    <span class="text-[11px] text-gray-400">/Month</span>
                </div>
            </div>

            {{-- Stock --}}
            <div class="p-6">
                <p class="text-[12px] text-gray-500 font-medium">Valeur du Stock</p>
                <p class="text-[28px] font-bold text-gray-900 mt-1 leading-none">{{ format_price($stats['stock_value']) }}</p>
                <div class="flex items-center gap-1.5 mt-2">
                    @if($stats['out_of_stock'] > 0)
                        <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold px-1.5 py-0.5 rounded bg-red-50 text-red-500">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7-7-7"/></svg>
                            {{ $stats['out_of_stock'] }} rupture(s)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold px-1.5 py-0.5 rounded bg-green-50 text-green-600">
                            Stock OK
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── ANALYTICS + TOP PRODUITS ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Order Analytics --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5"
             x-data="{
                 chart: null,
                 initChart(labels, revenues, orders) {
                     const canvas = document.getElementById('salesChart');
                     if (!canvas) return;
                     if (this.chart) { this.chart.destroy(); this.chart = null; }
                     this.chart = new Chart(canvas.getContext('2d'), {
                         type: 'line',
                         data: {
                             labels,
                             datasets: [
                                 {
                                     label: 'Mois précédent',
                                     data: revenues.map(v => v * 0.85),
                                     borderColor: '#d1d5db',
                                     borderWidth: 1.5,
                                     borderDash: [4, 4],
                                     tension: 0.4,
                                     pointRadius: 0,
                                     fill: false,
                                     yAxisID: 'y',
                                 },
                                 {
                                     label: 'Ce mois',
                                     data: revenues,
                                     borderColor: '#111827',
                                     borderWidth: 2,
                                     tension: 0.4,
                                     pointRadius: 0,
                                     pointHoverRadius: 4,
                                     pointHoverBackgroundColor: '#111827',
                                     fill: false,
                                     yAxisID: 'y',
                                 }
                             ]
                         },
                         options: {
                             responsive: true,
                             interaction: { mode: 'index', intersect: false },
                             plugins: {
                                 legend: { display: false },
                                 tooltip: {
                                     backgroundColor: '#1f2937',
                                     titleColor: '#f9fafb',
                                     bodyColor: '#d1d5db',
                                     padding: 10,
                                     cornerRadius: 8,
                                     borderWidth: 0,
                                 }
                             },
                             scales: {
                                 y: {
                                     grid: { color: '#f3f4f6', drawBorder: false },
                                     ticks: { color: '#9ca3af', font: { size: 10 }, maxTicksLimit: 5 }
                                 },
                                 x: {
                                     grid: { display: false },
                                     ticks: { color: '#9ca3af', font: { size: 10 } }
                                 }
                             }
                         }
                     });
                 }
             }"
             x-init="$nextTick(() => initChart(@js($salesChart['labels']), @js($salesChart['revenues']), @js($salesChart['orders'])))"
             x-ref="chartBox">
            <div class="flex items-start justify-between mb-1">
                <div>
                    <p class="text-[14px] font-semibold text-gray-900">Order Analytics</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-[22px] font-bold text-gray-900">{{ format_price($stats['monthly_revenue']) }}</span>
                        <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded
                                     {{ $stats['revenue_growth'] >= 0 ? 'text-green-600 bg-green-50' : 'text-red-500 bg-red-50' }}">
                            +{{ $stats['revenue_growth'] }}%
                        </span>
                        <span class="text-[11px] text-gray-400">
                            {{ match($period) { 'today' => "aujourd'hui", 'week' => 'cette semaine', default => 'ce mois' } }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-gray-50 border border-gray-200 rounded-lg p-0.5">
                        @foreach([['value' => 'today', 'label' => 'Auj.'], ['value' => 'week', 'label' => '7j'], ['value' => 'month', 'label' => 'Mois']] as $p)
                            <button wire:click="setPeriod('{{ $p['value'] }}')"
                                    class="px-3 py-1 rounded-md text-[11px] font-semibold transition-all
                                           {{ $period === $p['value'] ? 'bg-white shadow-sm text-gray-800' : 'text-gray-400 hover:text-gray-600' }}">
                                {{ $p['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 mb-4 text-[11px] text-gray-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>
                    Mois précédent
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-gray-800 inline-block"></span>
                    Ce mois
                </span>
                <div wire:loading class="ml-auto flex items-center gap-1 text-gray-400">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
            </div>

            @if(array_sum($salesChart['revenues']) > 0)
                <canvas id="salesChart" height="110"></canvas>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="text-[13px] text-gray-500 font-medium">Aucune donnée disponible</p>
                    <p class="text-[11px] text-gray-400 mt-1">Les statistiques de ventes apparaîtront ici</p>
                </div>
            @endif
        </div>

        {{-- Top produits --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-[14px] font-semibold text-gray-900 mb-4">Top Produits</p>
            <div class="space-y-3">
                @forelse($topProducts as $product)
                    @php
                        $maxSold   = $topProducts->first()->total_sold ?? 1;
                        $pct       = $maxSold > 0 ? round(($product->total_sold / $maxSold) * 100) : 0;
                        $mainImage = $product->images()->where('is_primary', true)->first()
                                  ?? $product->images()->first();
                    @endphp
                    <div wire:key="top-product-{{ $product->id }}" class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold flex-shrink-0
                                    {{ $loop->iteration === 1 ? 'bg-amber-100 text-amber-600' : ($loop->iteration === 2 ? 'bg-gray-100 text-gray-500' : 'bg-orange-100 text-orange-500') }}">
                            {{ $loop->iteration }}
                        </div>

                        @if($mainImage)
                            <img src="{{ asset('storage/' . $mainImage->path) }}"
                                 alt="{{ $product->name }}"
                                 class="w-10 h-10 rounded-lg object-cover flex-shrink-0 border border-gray-100">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 border border-gray-200">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <p class="text-[12px] font-medium text-gray-800 truncate">{{ $product->name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 h-1 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-gray-800" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-[10px] text-gray-400 whitespace-nowrap flex-shrink-0">{{ $product->total_sold }} vendu{{ $product->total_sold > 1 ? 's' : '' }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <p class="text-[12px] text-gray-500 font-medium">Aucune vente enregistrée</p>
                        <p class="text-[11px] text-gray-400 mt-1">Les produits les plus vendus apparaîtront ici</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── ORDER ACTIVITIES ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-[14px] font-semibold text-gray-900">Order Activities</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Suivez les activités récentes</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 border border-gray-200 rounded-lg px-3 py-1.5 text-[12px] text-gray-600 cursor-pointer hover:bg-gray-50 transition-colors">
                    <span>Status: Tous</span>
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="flex items-center gap-1.5 border border-gray-200 rounded-lg px-3 py-1.5 text-[12px] text-gray-600 cursor-pointer hover:bg-gray-50 transition-colors">
                    <span>Aujourd'hui</span>
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <a href="{{ route('admin.orders.index') }}"
                   class="flex items-center justify-center w-8 h-8 border border-gray-200 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox" class="w-3.5 h-3.5 rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                        </th>
                        <th class="px-4 py-3 text-left">
                            <span class="text-[11px] font-semibold text-gray-500 flex items-center gap-1">
                                Order ID
                                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </span>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <span class="text-[11px] font-semibold text-gray-500 flex items-center gap-1">
                                Client
                                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </span>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <span class="text-[11px] font-semibold text-gray-500 flex items-center gap-1">
                                Date & Heure
                                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </span>
                        </th>
                        <th class="px-4 py-3 text-right">
                            <span class="text-[11px] font-semibold text-gray-500">Montant</span>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <span class="text-[11px] font-semibold text-gray-500">Statut</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentOrders as $order)
                        @php
                            $statusMap = [
                                'pending'    => ['bg-amber-50 text-amber-600 border-amber-200',    'En attente', 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                'confirmed'  => ['bg-blue-50 text-blue-600 border-blue-200',       'Confirmé',   'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                'processing' => ['bg-purple-50 text-purple-600 border-purple-200', 'En cours',   'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                                'shipped'    => ['bg-cyan-50 text-cyan-600 border-cyan-200',       'Expédié',    'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
                                'delivered'  => ['bg-green-50 text-green-600 border-green-200',    'Livré',      'M5 13l4 4L19 7'],
                                'cancelled'  => ['bg-red-50 text-red-500 border-red-200',          'Annulé',     'M6 18L18 6M6 6l12 12'],
                                'refunded'   => ['bg-orange-50 text-orange-500 border-orange-200', 'Remboursé',  'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
                            ];
                            [$cls, $lbl, $icon] = $statusMap[$order->status] ?? ['bg-gray-100 text-gray-500 border-gray-200', $order->status_label ?? $order->status, 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
                        @endphp
                        <tr wire:key="order-{{ $order->id }}" class="hover:bg-gray-50 transition-colors group">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="w-3.5 h-3.5 rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="text-[12px] font-medium text-gray-800 hover:text-orange-500 transition-colors">
                                    #{{ $order->order_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-[12px] text-gray-700">
                                    {{ $order->customer
                                        ? $order->customer->first_name . ' ' . $order->customer->last_name
                                        : 'Client inconnu' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-[12px] text-gray-500">
                                    {{ $order->created_at->format('d M Y à H:i') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-[12px] font-semibold text-gray-800">
                                    {{ format_price($order->total) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div x-data="{
                                    open: false,
                                    currentStatus: '{{ $order->status }}',
                                    currentLabel: '{{ $lbl }}',
                                    currentClass: '{{ $cls }}',
                                    updating: false,
                                    async updateStatus(status, label, cls) {
                                        if (this.updating || status === this.currentStatus) return;
                                        this.updating = true;
                                        try {
                                            const res = await fetch('{{ route('admin.orders.status', $order) }}', {
                                                method: 'PATCH',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({ status })
                                            });
                                            if (res.ok) {
                                                this.currentStatus = status;
                                                this.currentLabel = label;
                                                this.currentClass = cls;
                                                this.open = false;
                                                window.showNotification && window.showNotification('Statut mis à jour', 'success');
                                            } else {
                                                throw new Error('Erreur');
                                            }
                                        } catch(e) {
                                            window.showNotification && window.showNotification('Erreur lors de la mise à jour', 'error');
                                        }
                                        this.updating = false;
                                    }
                                }"
                                @click.away="open = false"
                                class="relative">
                                    <button @click="open = !open"
                                            type="button"
                                            :disabled="updating"
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1.5 rounded-lg border transition-all hover:shadow-sm disabled:opacity-50"
                                            :class="currentClass">
                                        <span x-text="currentLabel"></span>
                                        <svg class="w-3 h-3 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                        <svg x-show="updating" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-1 z-50">

                                        @foreach(['pending' => 'En attente', 'confirmed' => 'Confirmé', 'processing' => 'En cours', 'shipped' => 'Expédié', 'delivered' => 'Livré'] as $statusKey => $statusLabel)
                                            @php
                                                [$statusCls, $statusLbl, $statusIcon] = $statusMap[$statusKey];
                                            @endphp
                                            <button @click="updateStatus('{{ $statusKey }}', '{{ $statusLbl }}', '{{ $statusCls }}')"
                                                    type="button"
                                                    :class="currentStatus === '{{ $statusKey }}' ? 'bg-gray-50' : 'hover:bg-gray-50'"
                                                    class="w-full px-3 py-2 text-left text-[12px] flex items-center gap-2 transition-colors">
                                                <svg class="w-4 h-4 {{ explode(' ', $statusCls)[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}"></path>
                                                </svg>
                                                <span class="flex-1">{{ $statusLbl }}</span>
                                                <svg x-show="currentStatus === '{{ $statusKey }}'" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        @endforeach

                                        <div class="border-t border-gray-100 my-1"></div>

                                        <button @click="updateStatus('cancelled', 'Annulé', 'bg-red-50 text-red-500 border-red-200')"
                                                type="button"
                                                class="w-full px-3 py-2 text-left text-[12px] flex items-center gap-2 hover:bg-gray-50 transition-colors text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            <span class="flex-1">Annuler</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-[12px] text-gray-400">
                                Aucune commande récente
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
            <span class="text-[11px] text-gray-400">{{ count($recentOrders) }} commandes affichées</span>
            <a href="{{ route('admin.orders.index') }}"
               class="text-[12px] text-orange-500 hover:text-orange-600 font-semibold">
                Voir toutes les commandes →
            </a>
        </div>
    </div>

    {{-- ── ALERTES STOCK ── --}}
    @if($lowStockProducts->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <p class="text-[14px] font-semibold text-gray-900">Alertes Stock</p>
            <a href="{{ route('admin.stock.alerts') }}" class="text-[12px] text-orange-500 hover:text-orange-600 font-semibold">Gérer →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($lowStockProducts as $product)
                <div wire:key="stock-{{ $product->id }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 transition-colors">
                    <div class="w-7 h-7 rounded-lg {{ $product->stock_quantity <= 0 ? 'bg-red-50' : 'bg-amber-50' }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 {{ $product->stock_quantity <= 0 ? 'text-red-400' : 'text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] font-medium text-gray-800 truncate">{{ $product->name }}</p>
                        <p class="text-[11px] text-gray-400">SKU: {{ $product->sku }}</p>
                    </div>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md flex-shrink-0
                                 {{ $product->stock_quantity <= 0 ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-600' }}">
                        {{ $product->stock_quantity <= 0 ? 'Rupture' : $product->stock_quantity . ' restant(s)' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('chart-data-updated', ({ labels, revenues, orders }) => {
        // Trouver le composant Alpine du chart et appeler initChart
        const box = document.querySelector('[x-ref="chartBox"]');
        if (box && box._x_dataStack) {
            const ctx = Alpine.$data(box);
            if (ctx && typeof ctx.initChart === 'function') {
                ctx.initChart(labels, revenues, orders);
            }
        }
    });
});
</script>
@endpush
