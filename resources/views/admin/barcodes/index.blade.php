@extends('layouts.admin')
@section('title', 'Codes-barres & QR Codes')
@section('page-title', 'Codes-barres & QR Codes')

@section('content')
<div class="space-y-5" x-data="barcodesPage()">

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $totalProducts   = $products->total();
            $withBarcode     = \App\Models\Product::whereNotNull('barcode')->where('barcode','!=','')->count();
            $withoutBarcode  = \App\Models\Product::where(fn($q) => $q->whereNull('barcode')->orWhere('barcode',''))->count();
            $totalVariants   = \App\Models\ProductVariant::count();
        @endphp
        @foreach([
            ['Produits total',        $totalProducts,  'bg-orange-50 text-orange-600',  'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['Avec code-barres',      $withBarcode,    'bg-green-50 text-green-600',    'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Sans code-barres',      $withoutBarcode, 'bg-red-50 text-red-600',        'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ['Variantes total',       $totalVariants,  'bg-blue-50 text-blue-600',      'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
        ] as [$label, $val, $cls, $icon])
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $cls }} flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/></svg>
            </div>
            <div>
                <p class="text-xl font-black text-gray-900">{{ number_format($val) }}</p>
                <p class="text-[11px] text-gray-400 font-medium">{{ $label }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Barre d'outils --}}
    <div class="flex flex-wrap items-center gap-3">
        {{-- Recherche --}}
        <form method="GET" class="flex items-center gap-2 flex-1 min-w-[280px]">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Nom, SKU, code-barres…"
                    class="w-full pl-9 pr-4 h-9 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <button type="submit" class="h-9 px-4 bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition">Chercher</button>
        </form>

        {{-- Générer manquants --}}
        <form method="POST" action="{{ route('admin.barcodes.bulk-generate') }}" id="bulk-form">
            @csrf
            <button type="submit" class="h-9 px-4 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Générer les manquants
            </button>
        </form>

        {{-- Scanner --}}
        <button @click="scannerOpen = true; $nextTick(() => $refs.scanInput.focus())"
            class="h-9 px-4 bg-violet-600 text-white text-[13px] font-semibold rounded-lg hover:bg-violet-700 transition inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            Scanner
        </button>

        {{-- Imprimer sélection --}}
        <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 h-9 bg-white">
            <select x-model="printFormat" class="text-[12px] text-gray-700 bg-transparent border-0 focus:ring-0 pr-1">
                <option value="50x30">50×30 mm (B21)</option>
                <option value="40x30">40×30 mm</option>
                <option value="60x40">60×40 mm</option>
                <option value="80x50">80×50 mm</option>
                <option value="40x12">40×12 mm (D11)</option>
                <option value="57x32">57×32 mm</option>
            </select>
            <span class="text-gray-300 text-xs">×</span>
            <input type="number" x-model.number="printQty" min="1" max="99" class="w-10 text-[12px] text-center border-0 focus:ring-0 p-0">
            <button @click="printSelected()"
                class="h-6 px-2.5 bg-green-600 text-white text-[11px] font-semibold rounded-md hover:bg-green-700 transition">
                Imprimer
            </button>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        {{-- Header sélection --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <input type="checkbox" id="select-all" @change="toggleAll($event.target.checked)"
                    class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 focus:ring-offset-0">
                <label for="select-all" class="text-[12px] text-gray-500 cursor-pointer">Tout sélectionner</label>
                <span x-show="selected.length > 0" class="text-[12px] font-semibold text-orange-600"
                      x-text="selected.length + ' sélectionné(s)'"></span>
            </div>
            <div class="flex items-center gap-2" x-show="selected.length > 0">
                <button @click="bulkGenerate()"
                    class="h-7 px-3 text-[12px] font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition">
                    Générer barcodes
                </button>
                <button @click="printSelected()"
                    class="h-7 px-3 text-[12px] font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                    Imprimer étiquettes
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="w-10 px-4 py-3"></th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Code-barres</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Prix</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3">
                            <input type="checkbox" value="{{ $product->id }}"
                                x-model="selected"
                                class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 focus:ring-offset-0">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                @if($product->primary_image_url)
                                    <img src="{{ $product->primary_image_url }}" class="w-9 h-9 rounded-lg object-cover border border-gray-100 flex-shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-lg bg-gray-100 border border-gray-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate max-w-[200px]">{{ $product->name }}</p>
                                    @if($product->variants->count() > 0)
                                    <p class="text-[11px] text-gray-400">{{ $product->variants->count() }} variante(s)</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono text-[12px] text-gray-500">{{ $product->sku ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($product->barcode)
                                <span class="font-mono text-[12px] text-gray-800">{{ $product->barcode }}</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] text-red-500 font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01"/></svg>
                                    Non généré
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-900 tabular-nums">{{ format_price($product->sale_price) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                {{-- Voir barcode --}}
                                <button @click="showBarcode({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                    class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition"
                                    title="Voir code-barres">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                </button>
                                {{-- QR Code --}}
                                <button @click="showQrCode({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                    class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition"
                                    title="Voir QR Code">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                                {{-- Imprimer --}}
                                <button @click="printOne({{ $product->id }})"
                                    class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition"
                                    title="Imprimer étiquette">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Variantes (sous-lignes) --}}
                    @if($product->variants->count() > 0)
                    @foreach($product->variants as $variant)
                    <tr class="bg-gray-50/40 border-b border-gray-50 group/v">
                        <td class="px-4 py-2 pl-10">
                            <input type="checkbox" value="v:{{ $variant->id }}"
                                x-model="selected"
                                class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 focus:ring-offset-0 scale-90">
                        </td>
                        <td class="px-4 py-2 pl-10">
                            @php
                                $vColor = $variant->attributeValues->firstWhere(fn($av) => $av->attribute && $av->attribute->slug === 'couleur');
                                $vLabel = $variant->name ?: ($vColor ? $vColor->value : 'Variante');
                            @endphp
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-gray-400">└</span>
                                @if($vColor && $vColor->color_code)
                                    <span class="w-3.5 h-3.5 rounded-full border border-gray-200 flex-shrink-0" style="background:{{ $vColor->color_code }}"></span>
                                @endif
                                <span class="text-[12px] text-gray-600">{{ $vLabel }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2 font-mono text-[11px] text-gray-400">{{ $variant->sku ?? '—' }}</td>
                        <td class="px-4 py-2">
                            @if($variant->barcode)
                                <span class="font-mono text-[11px] text-gray-600">{{ $variant->barcode }}</span>
                            @else
                                <span class="text-[11px] text-gray-400 italic">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-[12px] text-gray-500 tabular-nums">
                            {{ $variant->sale_price ? format_price($variant->sale_price) : '—' }}
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex items-center justify-center gap-1 opacity-0 group-hover/v:opacity-100 transition-opacity">
                                @if($variant->barcode)
                                <button @click="showVariantBarcode('{{ $variant->barcode }}', '{{ addslashes($vLabel) }}')"
                                    class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded transition"
                                    title="Voir code-barres">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @endif
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-[13px]">Aucun produit trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $products->links() }}</div>
        @endif
    </div>

{{-- ── MODAL code-barres / QR ── --}}
<div x-cloak x-show="modal.open"
     class="fixed inset-0 z-[9990] flex items-center justify-center"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="modal.open = false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="scale-95 opacity-0"
         x-transition:enter-end="scale-100 opacity-100"
         @click.stop>
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <p class="text-[14px] font-bold text-gray-900" x-text="modal.title"></p>
            <button @click="modal.open = false" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:bg-gray-100 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 text-center">
            <template x-if="modal.loading">
                <div class="py-8 flex justify-center">
                    <svg class="w-8 h-8 text-orange-500 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
            </template>
            <template x-if="!modal.loading && modal.content">
                <div>
                    <div x-html="modal.content" class="mb-3"></div>
                    <p class="font-mono text-[13px] text-gray-600 mt-2" x-text="modal.code"></p>
                </div>
            </template>
        </div>
        <div class="flex gap-2 px-5 pb-5">
            <button @click="printModal()"
                class="flex-1 h-9 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition">
                Imprimer
            </button>
            <button @click="modal.open = false"
                class="h-9 px-4 border border-gray-200 text-[13px] text-gray-600 rounded-lg hover:bg-gray-50 transition">
                Fermer
            </button>
        </div>
    </div>
</div>

{{-- ── MODAL Scanner ── --}}
<div x-cloak x-show="scannerOpen"
     class="fixed inset-0 z-[9990] flex items-center justify-center"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="scannerOpen = false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4"
         @click.stop
         @keydown.escape.window="scannerOpen = false">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <p class="text-[14px] font-bold text-gray-900">Scanner un code</p>
            <button @click="scannerOpen = false" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:bg-gray-100 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <input type="text" x-ref="scanInput" x-model="scanCode"
                @keydown.enter.prevent="doScan()"
                placeholder="Scannez ou tapez le code…"
                class="w-full px-4 py-3 text-[14px] font-mono border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500">

            <template x-if="scanResult">
                <div :class="scanResult.found ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'"
                     class="p-4 rounded-xl border">
                    <template x-if="scanResult.found">
                        <div>
                            <p class="text-[13px] font-bold text-green-700 mb-2">✓ Produit trouvé</p>
                            <div class="space-y-1">
                                <p class="text-[13px] font-semibold text-gray-900" x-text="scanResult.data.name"></p>
                                <p class="text-[12px] text-gray-500">SKU : <span class="font-mono" x-text="scanResult.data.sku || 'N/A'"></span></p>
                                <p class="text-[12px] text-gray-500">Stock : <span class="font-semibold text-gray-700" x-text="scanResult.data.stock + ' pcs'"></span></p>
                                <p class="text-[13px] font-bold text-orange-600" x-text="new Intl.NumberFormat('fr-FR').format(scanResult.data.price) + ' F CFA'"></p>
                            </div>
                        </div>
                    </template>
                    <template x-if="!scanResult.found">
                        <p class="text-[13px] font-medium text-red-600">Aucun produit trouvé pour ce code.</p>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>

</div>{{-- fin x-data="barcodesPage()" --}}

@push('scripts')
<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('barcodesPage', function() {
        return {
            selected: [],
            printFormat: '50x30',
            printQty: 1,
            scannerOpen: false,
            scanCode: '',
            scanResult: null,
            modal: { open: false, title: '', loading: false, content: '', code: '' },

            toggleAll(checked) {
                const checkboxes = document.querySelectorAll('input[type=checkbox][value]');
                this.selected = checked ? [...checkboxes].map(cb => cb.value) : [];
            },

            async doScan() {
                const code = this.scanCode.trim();
                if (!code) return;
                try {
                    const res = await fetch('{{ route("admin.barcodes.scan") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ code }),
                    });
                    this.scanResult = await res.json();
                } catch (e) {
                    this.scanResult = { found: false };
                }
                this.scanCode = '';
            },

            async showBarcode(productId, name) {
                this.modal = { open: true, title: 'Code-barres — ' + name, loading: true, content: '', code: '' };
                try {
                    const res  = await fetch('/admin/barcodes/' + productId + '/generate');
                    const data = await res.json();
                    this.modal.content = '<img src="' + data.barcode_svg + '" class="mx-auto" style="height:70px">';
                    this.modal.code    = data.barcode;
                    this.modal.loading = false;
                } catch(e) {
                    this.modal.content = '<p class="text-red-500 text-sm">Erreur</p>';
                    this.modal.loading = false;
                }
            },

            showVariantBarcode(barcode, name) {
                this.modal = { open: true, title: name, loading: false, code: barcode, content: '' };
                // Génération SVG locale via URL encodée (fallback visuel)
                this.modal.content = `<p class="font-mono text-lg text-gray-800">${barcode}</p>`;
            },

            async showQrCode(productId, name) {
                this.modal = { open: true, title: 'QR Code — ' + name, loading: true, content: '', code: '' };
                try {
                    const res  = await fetch('/admin/barcodes/' + productId + '/qrcode');
                    const data = await res.json();
                    if (data.success) {
                        this.modal.content = '<img src="' + data.qr_code + '" class="w-40 h-40 mx-auto">'
                            + '<p class="text-[11px] text-gray-400 mt-2 break-all">' + data.qr_url + '</p>';
                        this.modal.code = data.product.sku || '';
                    }
                    this.modal.loading = false;
                } catch(e) {
                    this.modal.content = '<p class="text-red-500 text-sm">Erreur</p>';
                    this.modal.loading = false;
                }
            },

            printModal() {
                window.print();
            },

            printOne(productId) {
                const url = '{{ route("admin.barcodes.print-labels") }}?products=' + productId
                    + '&format=' + encodeURIComponent(this.printFormat)
                    + '&quantity=' + this.printQty;
                window.open(url, '_blank');
            },

            printSelected() {
                const ids = this.selected.filter(v => !v.startsWith('v:'));
                if (ids.length === 0) {
                    alert('Sélectionnez au moins un produit.');
                    return;
                }
                const url = '{{ route("admin.barcodes.print-labels") }}?products=' + ids.join(',')
                    + '&format=' + encodeURIComponent(this.printFormat)
                    + '&quantity=' + this.printQty;
                window.open(url, '_blank');
            },

            async bulkGenerate() {
                const ids = this.selected.filter(v => !v.startsWith('v:')).map(Number);
                if (ids.length === 0) return;
                const csrf = document.querySelector('meta[name=csrf-token]').content;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.barcodes.bulk-generate") }}';
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden'; csrfInput.name = '_token'; csrfInput.value = csrf;
                form.appendChild(csrfInput);
                ids.forEach(id => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = 'product_ids[]'; inp.value = id;
                    form.appendChild(inp);
                });
                document.body.appendChild(form);
                form.submit();
            },
        };
    });
});
</script>
@endpush
@endsection
