@extends('layouts.admin')

@section('title', 'Inventaire')
@section('page-title', 'Inventaire des stocks')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <a href="{{ route('admin.stock.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
    </div>

    <!-- Recherche -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, SKU ou code-barres..." class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">Rechercher</button>
        </form>
    </div>

    <!-- Formulaire d'ajustement -->
    <form method="POST" action="{{ route('admin.stock.adjust-inventory') }}" id="inventoryForm">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Produits en stock</h3>
                <div class="flex gap-2">
                    <input type="text" name="reason" placeholder="Raison de l'ajustement (ex: Inventaire mensuel)" class="px-4 py-2 border border-slate-300 rounded-xl text-sm w-64" required>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer les ajustements
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Produit</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">SKU</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Stock système</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Stock réel</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Écart</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200" x-data="inventoryManager()">
                        @forelse($products as $product)
                            <tr class="hover:bg-slate-50" x-data="{ newQty: {{ $product->stock_quantity }}, original: {{ $product->stock_quantity }} }">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($product->primary_image_url)
                                            <img src="{{ $product->primary_image_url }}" alt="" class="w-10 h-10 rounded-lg object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-slate-900">{{ Str::limit($product->name, 35) }}</p>
                                            @if($product->variants->count() > 0)
                                                <p class="text-xs text-slate-500">{{ $product->variants->count() }} variante(s)</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-mono text-sm">{{ $product->sku ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-semibold text-slate-900">{{ $product->stock_quantity }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <input type="hidden" name="adjustments[{{ $loop->index }}][product_id]" value="{{ $product->id }}">
                                    <input 
                                        type="number" 
                                        name="adjustments[{{ $loop->index }}][new_quantity]" 
                                        x-model="newQty"
                                        min="0" 
                                        class="w-24 px-3 py-2 border border-slate-300 rounded-lg text-center font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                        :class="newQty != original ? 'border-amber-400 bg-amber-50' : ''"
                                    >
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span 
                                        class="font-bold"
                                        :class="{
                                            'text-green-600': newQty > original,
                                            'text-red-600': newQty < original,
                                            'text-slate-400': newQty == original
                                        }"
                                        x-text="newQty - original > 0 ? '+' + (newQty - original) : (newQty - original)"
                                    ></span>
                                </td>
                            </tr>
                            
                            {{-- Variantes --}}
                            @foreach($product->variants as $variant)
                            <tr class="hover:bg-slate-50 bg-slate-25" x-data="{ newQty: {{ $variant->stock_quantity }}, original: {{ $variant->stock_quantity }} }">
                                <td class="px-6 py-3 pl-16">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        <span class="text-sm text-slate-600">{{ $variant->sku }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ $variant->sku }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="font-medium text-slate-700">{{ $variant->stock_quantity }}</span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <input type="hidden" name="adjustments[{{ $loop->parent->index }}_{{ $loop->index }}][product_id]" value="{{ $product->id }}">
                                    <input type="hidden" name="adjustments[{{ $loop->parent->index }}_{{ $loop->index }}][variant_id]" value="{{ $variant->id }}">
                                    <input 
                                        type="number" 
                                        name="adjustments[{{ $loop->parent->index }}_{{ $loop->index }}][new_quantity]" 
                                        x-model="newQty"
                                        min="0" 
                                        class="w-20 px-2 py-1.5 border border-slate-300 rounded-lg text-center text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                        :class="newQty != original ? 'border-amber-400 bg-amber-50' : ''"
                                    >
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span 
                                        class="font-medium text-sm"
                                        :class="{
                                            'text-green-600': newQty > original,
                                            'text-red-600': newQty < original,
                                            'text-slate-400': newQty == original
                                        }"
                                        x-text="newQty - original > 0 ? '+' + (newQty - original) : (newQty - original)"
                                    ></span>
                                </td>
                            </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    Aucun produit trouvé
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $products->links() }}
            </div>
            @endif
        </div>
    </form>
</div>

<script>
function inventoryManager() {
    return {
        // Fonctions utilitaires si nécessaires
    }
}
</script>
@endsection

