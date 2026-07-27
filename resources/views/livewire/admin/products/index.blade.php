<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Produits</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">{{ $products->total() }} produit(s) au total</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.import-export.index') }}" class="h-9 px-4 flex items-center gap-2 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Importer
            </a>
            <a href="{{ route('admin.products.create') }}" class="h-9 px-4 flex items-center gap-2 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau produit
            </a>
        </div>
    </div>

    {{-- Filters & Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

        {{-- Filters --}}
        <div class="p-4 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row gap-3">

                {{-- Search --}}
                <div class="flex-1 relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher par nom, SKU..."
                        class="w-full h-9 px-3 pr-8 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <div wire:loading.delay wire:target="search" class="absolute right-2.5 top-1/2 -translate-y-1/2">
                        <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>
                </div>

                {{-- Status filter --}}
                <select wire:model.live="status" class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actif</option>
                    <option value="draft">Brouillon</option>
                    <option value="archived">Archivé</option>
                </select>

                {{-- Category filter --}}
                <select wire:model.live="category" class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Toutes catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                {{-- Stock filter --}}
                <select wire:model.live="stock" class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tout stock</option>
                    <option value="low">Stock faible</option>
                    <option value="out">Rupture</option>
                </select>

                {{-- Reset button --}}
                @if($search || $status || $category || $stock)
                <button
                    type="button"
                    wire:click="$set('search', ''); $set('status', ''); $set('category', ''); $set('stock', '')"
                    class="h-9 px-4 flex items-center justify-center border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition"
                    title="Réinitialiser les filtres">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                @endif
            </div>

            {{-- Loading indicator --}}
            <div wire:loading.delay wire:target="search,status,category,stock" class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Mise à jour des résultats...
            </div>
        </div>

        {{-- Loading overlay --}}
        <div wire:loading.delay wire:target="search,status,category,stock" class="flex items-center justify-center py-8 text-gray-400 text-[13px]">
            <svg class="animate-spin w-4 h-4 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Chargement...
        </div>

        {{-- Table --}}
        <div wire:loading.remove wire:target="search,status,category,stock" class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Produit</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">SKU</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">Prix</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Stock</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Variantes</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Statut</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr wire:key="product-{{ $product->id }}" class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    @if($product->images->where('is_primary', true)->first())
                                        <img src="{{ asset('storage/' . $product->images->where('is_primary', true)->first()->path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $product->category?->name ?? 'Sans catégorie' }}</p>
                                </div>
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $product->sku }}</td>
                        <td class="px-4 py-3 text-right">
                            <p class="font-semibold text-gray-900 tabular-nums">{{ number_format($product->sale_price, 0, ',', ' ') }} F</p>
                            @if($product->compare_price && $product->compare_price > $product->sale_price)
                                <p class="text-xs text-gray-400 line-through tabular-nums">{{ number_format($product->compare_price, 0, ',', ' ') }} F</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($product->stock_quantity <= 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border bg-red-50 text-red-700 border-red-200">
                                    Rupture
                                </span>
                            @elseif($product->stock_quantity <= $product->stock_alert_threshold)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border bg-yellow-50 text-yellow-700 border-yellow-200">
                                    {{ $product->stock_quantity }}
                                </span>
                            @else
                                <span class="text-gray-700 font-medium tabular-nums">{{ $product->stock_quantity }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 tabular-nums">
                            {{ $product->variants->count() }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = [
                                    'active'   => 'bg-green-50 text-green-700 border-green-200',
                                    'draft'    => 'bg-gray-50 text-gray-700 border-gray-200',
                                    'archived' => 'bg-red-50 text-red-700 border-red-200',
                                ];
                                $statusLabels = [
                                    'active'   => 'Actif',
                                    'draft'    => 'Brouillon',
                                    'archived' => 'Archivé',
                                ];
                                $color = $statusColors[$product->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                $label = $statusLabels[$product->status] ?? ucfirst($product->status);
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $color }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center gap-1 text-gray-600 hover:text-blue-600 transition">
                                Modifier
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Aucun produit trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif

    </div>

</div>
