<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 hover:text-gray-900 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Rapports
            </a>
            <h1 class="text-xl font-bold text-gray-900">Rapport stock</h1>
        </div>
        <a href="{{ route('admin.reports.stock.export-csv') }}"
           class="h-9 px-4 inline-flex items-center gap-2 bg-white border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
    </div>

    {{-- KPI stock --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
            <div class="p-5">
                <p class="text-[12px] text-gray-500 mb-1">Valeur stock (coût)</p>
                <p class="text-2xl font-bold text-gray-900">{{ format_price($stockValue->cost_value ?? 0) }}</p>
                <p class="text-[12px] text-gray-400 mt-1">{{ number_format($stockValue->total_units ?? 0, 0, ',', ' ') }} unités</p>
            </div>
            <div class="p-5">
                <p class="text-[12px] text-gray-500 mb-1">Valeur stock (vente)</p>
                <p class="text-2xl font-bold text-green-600">{{ format_price($stockValue->sale_value ?? 0) }}</p>
                <p class="text-[12px] text-gray-400 mt-1">Marge potentielle</p>
            </div>
            <div class="p-5">
                <p class="text-[12px] text-gray-500 mb-1">Alertes stock</p>
                <p class="text-2xl font-bold text-amber-600">{{ $outOfStock->count() + $lowStock->count() }}</p>
                <p class="text-[12px] text-gray-400 mt-1">
                    <span class="text-red-500 font-medium">{{ $outOfStock->count() }} rupture(s)</span> ·
                    <span class="text-amber-500 font-medium">{{ $lowStock->count() }} alerte(s)</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Ruptures + Alertes --}}
    <div class="grid lg:grid-cols-2 gap-5">

        {{-- Ruptures de stock --}}
        <div class="bg-white rounded-xl border border-red-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-red-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-[14px] font-semibold text-red-900">Ruptures de stock <span class="text-red-500">({{ $outOfStock->count() }})</span></h3>
            </div>
            @if($outOfStock->count() > 0)
            <div class="divide-y divide-red-50 max-h-80 overflow-y-auto">
                @foreach($outOfStock as $product)
                <div class="px-5 py-3.5 flex items-center justify-between hover:bg-red-50/50 transition-colors">
                    <div>
                        <p class="text-[13px] font-medium text-gray-900">{{ $product->name }}</p>
                        <p class="text-[11px] text-gray-400">SKU: {{ $product->sku ?? 'N/A' }}</p>
                    </div>
                    <a href="{{ route('admin.products.edit', $product) }}" class="text-[12px] text-blue-600 hover:underline font-medium">Modifier</a>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center">
                <svg class="w-10 h-10 mx-auto text-green-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-[13px] text-green-600 font-medium">Aucune rupture de stock</p>
            </div>
            @endif
        </div>

        {{-- Stock faible --}}
        <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-amber-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-[14px] font-semibold text-amber-900">Stock faible <span class="text-amber-500">({{ $lowStock->count() }})</span></h3>
            </div>
            @if($lowStock->count() > 0)
            <div class="divide-y divide-amber-50 max-h-80 overflow-y-auto">
                @foreach($lowStock as $product)
                <div class="px-5 py-3.5 flex items-center justify-between hover:bg-amber-50/50 transition-colors">
                    <div>
                        <p class="text-[13px] font-medium text-gray-900">{{ $product->name }}</p>
                        <p class="text-[11px] text-gray-500">
                            Stock : <span class="font-semibold text-amber-600">{{ $product->stock_quantity }}</span>
                            / Seuil : {{ $product->stock_alert_threshold }}
                        </p>
                    </div>
                    <a href="{{ route('admin.products.edit', $product) }}" class="text-[12px] text-blue-600 hover:underline font-medium">Modifier</a>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center">
                <svg class="w-10 h-10 mx-auto text-green-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-[13px] text-green-600 font-medium">Tous les stocks sont OK</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Rotation du stock --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-[14px] font-semibold text-gray-900">Rotation du stock <span class="text-gray-400 font-normal text-[13px]">30 derniers jours</span></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase">Produit</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase">Vendus (30j)</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase">Stock actuel</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase">Jours de stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($stockRotation as $product)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5 font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($product->name, 45) }}</td>
                        <td class="px-5 py-3.5 text-right text-gray-700 font-medium">{{ $product->sold_30d }}</td>
                        <td class="px-5 py-3.5 text-right text-gray-600">{{ $product->stock_quantity }}</td>
                        <td class="px-5 py-3.5 text-right">
                            @if($product->days_of_stock !== null)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                    {{ $product->days_of_stock < 7 ? 'bg-red-100 text-red-700' : ($product->days_of_stock < 30 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">
                                    {{ $product->days_of_stock }}j
                                </span>
                            @else
                                <span class="text-gray-300">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-12 text-center text-[13px] text-gray-400">Aucune donnée de vente</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
