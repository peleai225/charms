{{-- Tableau des variantes existantes --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <h2 class="text-sm font-semibold text-gray-900">Variantes</h2>
            <span id="variants-count" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                {{ $product->variants->count() > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $product->variants->count() }} variante(s)
            </span>
        </div>
        @if($product->variants->count() > 0)
        <span class="text-[12px] text-gray-400">{{ $product->variants->sum('stock_quantity') }} pcs au total</span>
        @endif
    </div>

    @if($product->variants->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Variante</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">SKU / Barcode</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-28">Stock</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-32">Prix vente</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-32">Prix achat</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-24">Statut</th>
                    <th class="px-4 py-3 w-20 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($product->variants as $variant)
                @php
                    $vColor  = $variant->attributeValues->firstWhere(fn($v) => $v->attribute && $v->attribute->slug === 'couleur');
                    $vOthers = $variant->attributeValues->filter(fn($v) => $v->attribute && $v->attribute->slug !== 'couleur')->values();
                    $variantLabel = $variant->name ?: ($vColor ? $vColor->value : 'Variante');
                @endphp
                <tr id="variant-row-{{ $variant->id }}"
                    class="hover:bg-gray-50/60 transition-colors group relative"
                    :class="saving[{{ $variant->id }}] ? 'bg-blue-50/30' : ''">

                    {{-- Overlay état sauvegarde --}}
                    <td colspan="7" x-show="saving[{{ $variant->id }}]" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 pointer-events-none">
                        <div class="flex items-center justify-center h-full">
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-white rounded-lg shadow-sm border border-gray-200">
                                <svg class="w-4 h-4 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span class="text-[12px] font-medium text-gray-700">Enregistrement...</span>
                            </div>
                        </div>
                    </td>

                    {{-- Identité --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="relative flex-shrink-0">
                                <img id="variant-img-{{ $variant->id }}"
                                     src="{{ $variant->image ? asset('storage/' . $variant->image) : '' }}"
                                     class="w-9 h-9 rounded-lg object-cover border border-gray-200 flex-shrink-0 {{ $variant->image ? '' : 'hidden' }}">
                                @if(!$variant->image)
                                    @if($vColor && $vColor->color_code)
                                        <span id="variant-placeholder-{{ $variant->id }}" class="w-9 h-9 rounded-lg border border-gray-200 flex-shrink-0 inline-block" style="background:{{ $vColor->color_code }}"></span>
                                    @else
                                        <span id="variant-placeholder-{{ $variant->id }}" class="w-9 h-9 rounded-lg bg-gray-100 border border-gray-200 flex-shrink-0 inline-block"></span>
                                    @endif
                                    {{-- Badge image manquante --}}
                                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-orange-400 rounded-full border-2 border-white"
                                         title="Image manquante"></div>
                                @endif
                            </div>
                            <div class="flex flex-col gap-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-1">
                                    @if($vColor)
                                        <span class="font-medium text-gray-900">{{ $vColor->value }}</span>
                                    @endif
                                    @foreach($vOthers as $av)
                                        <span class="text-[11px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded whitespace-nowrap">{{ $av->value }}</span>
                                    @endforeach
                                    @if(!$vColor && $vOthers->isEmpty())
                                        <span class="text-gray-600">{{ $variant->name ?: 'Variante' }}</span>
                                    @endif
                                </div>
                                {{-- Badges état inline --}}
                                <div class="flex items-center gap-1">
                                    @if(!$variant->is_active)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded text-[10px] font-medium">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- SKU + Barcode --}}
                    <td class="px-4 py-3">
                        <p class="font-mono text-[12px] text-gray-700">{{ $variant->sku }}</p>
                        @if($variant->barcode)
                        <p class="font-mono text-[11px] text-gray-400 mt-0.5">{{ $variant->barcode }}</p>
                        @endif
                    </td>

                    {{-- Stock inline --}}
                    <td class="px-4 py-3 text-center"
                        x-data="{ editing: false, val: {{ $variant->stock_quantity }}, justSaved: false }">
                        <template x-if="!editing">
                            <div class="flex flex-col items-center gap-1">
                                <div class="flex items-center gap-1.5">
                                    <span id="stock-badge-{{ $variant->id }}"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold
                                            {{ $variant->stock_quantity <= 0 ? 'bg-red-100 text-red-700 ring-1 ring-red-200' : ($variant->stock_quantity <= 5 ? 'bg-amber-100 text-amber-700 ring-1 ring-amber-200' : 'bg-green-100 text-green-700') }}">
                                        @if($variant->stock_quantity <= 0)
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                            </svg>
                                            Rupture
                                        @elseif($variant->stock_quantity <= 5)
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18 9 9 0 010-18z"/>
                                            </svg>
                                            {{ $variant->stock_quantity }} pcs
                                        @else
                                            {{ $variant->stock_quantity }} pcs
                                        @endif
                                    </span>
                                    <button type="button" @click="editing = true"
                                        class="opacity-0 group-hover:opacity-100 w-5 h-5 flex items-center justify-center text-gray-400 hover:text-blue-600 transition-opacity">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                </div>
                                <span x-show="justSaved" x-transition.opacity.duration.300ms
                                    class="inline-flex items-center gap-1 text-[10px] text-green-600 font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Sauvegardé
                                </span>
                            </div>
                        </template>
                        <template x-if="editing">
                            <div class="flex items-center justify-center gap-1">
                                <input type="number" x-model.number="val" min="0"
                                    class="w-16 h-7 px-2 text-[12px] border border-blue-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
                                    @keydown.enter.prevent="patchVariant({{ $variant->id }}, { stock_quantity: val }); editing = false; justSaved = true; setTimeout(() => justSaved = false, 2000)"
                                    @keydown.escape="editing = false"
                                    x-init="$nextTick(() => $el.focus())">
                                <button type="button" @click="patchVariant({{ $variant->id }}, { stock_quantity: val }); editing = false; justSaved = true; setTimeout(() => justSaved = false, 2000)"
                                    class="w-7 h-7 flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white rounded shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="editing = false"
                                    class="w-7 h-7 flex items-center justify-center border border-gray-200 text-gray-500 hover:bg-gray-50 rounded">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </td>

                    {{-- Prix vente inline --}}
                    <td class="px-4 py-3 text-center"
                        x-data="{ editing: false, val: '{{ $variant->sale_price ?? '' }}' }">
                        <template x-if="!editing">
                            <div class="flex items-center justify-center gap-1.5">
                                <span id="price-badge-{{ $variant->id }}" class="text-gray-700 tabular-nums text-[12px]">
                                    {{ $variant->sale_price !== null ? number_format($variant->sale_price, 0, ',', ' ') . ' F' : '—' }}
                                </span>
                                <button type="button" @click="editing = true"
                                    class="opacity-0 group-hover:opacity-100 w-5 h-5 flex items-center justify-center text-gray-400 hover:text-orange-500 transition-opacity">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="editing">
                            <div class="flex items-center justify-center gap-1">
                                <input type="number" x-model="val" min="0" step="1" placeholder="—"
                                    class="w-24 h-7 px-2 text-[12px] border border-orange-300 rounded focus:outline-none focus:ring-1 focus:ring-orange-500 text-center"
                                    @keydown.enter.prevent="patchVariant({{ $variant->id }}, { sale_price: val || null }); editing = false"
                                    @keydown.escape="editing = false"
                                    x-init="$nextTick(() => $el.focus())">
                                <button type="button" @click="patchVariant({{ $variant->id }}, { sale_price: val || null }); editing = false"
                                    class="w-7 h-7 flex items-center justify-center bg-orange-600 hover:bg-orange-700 text-white rounded">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="editing = false"
                                    class="w-7 h-7 flex items-center justify-center border border-gray-200 text-gray-500 hover:bg-gray-50 rounded">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </td>

                    {{-- Prix achat inline --}}
                    <td class="px-4 py-3 text-center"
                        x-data="{ editing: false, val: '{{ $variant->purchase_price ?? '' }}' }">
                        <template x-if="!editing">
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="text-gray-500 tabular-nums text-[12px]">
                                    {{ $variant->purchase_price !== null ? number_format($variant->purchase_price, 0, ',', ' ') . ' F' : '—' }}
                                </span>
                                <button type="button" @click="editing = true"
                                    class="opacity-0 group-hover:opacity-100 w-5 h-5 flex items-center justify-center text-gray-400 hover:text-orange-500 transition-opacity">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="editing">
                            <div class="flex items-center justify-center gap-1">
                                <input type="number" x-model="val" min="0" step="1" placeholder="—"
                                    class="w-24 h-7 px-2 text-[12px] border border-orange-300 rounded focus:outline-none focus:ring-1 focus:ring-orange-500 text-center"
                                    @keydown.enter.prevent="patchVariant({{ $variant->id }}, { purchase_price: val || null }); editing = false"
                                    @keydown.escape="editing = false"
                                    x-init="$nextTick(() => $el.focus())">
                                <button type="button" @click="patchVariant({{ $variant->id }}, { purchase_price: val || null }); editing = false"
                                    class="w-7 h-7 flex items-center justify-center bg-orange-600 hover:bg-orange-700 text-white rounded">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="editing = false"
                                    class="w-7 h-7 flex items-center justify-center border border-gray-200 text-gray-500 hover:bg-gray-50 rounded">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </td>

                    {{-- Statut toggle --}}
                    <td class="px-4 py-3 text-center" x-data="{ toggleSaved: false }">
                        <button type="button"
                            id="active-badge-{{ $variant->id }}"
                            @click="patchVariant({{ $variant->id }}, { is_active: document.getElementById('active-badge-{{ $variant->id }}').textContent.trim() !== 'Active' }); toggleSaved = true; setTimeout(() => toggleSaved = false, 2000)"
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold cursor-pointer hover:scale-105 active:scale-95 transition-all
                                {{ $variant->is_active ? 'bg-green-100 text-green-700 ring-1 ring-green-200' : 'bg-gray-100 text-gray-500' }}">
                            @if($variant->is_active)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endif
                            {{ $variant->is_active ? 'Active' : 'Inactive' }}
                        </button>
                        <span x-show="toggleSaved" x-transition.opacity.duration.300ms
                            class="block mt-1 text-[10px] text-green-600 font-medium">
                            Mis à jour
                        </span>
                    </td>

                    {{-- Actions : édition complète + suppression --}}
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button"
                                @click="openEditDrawer({{ json_encode(['id' => $variant->id, 'sku' => $variant->sku, 'barcode' => $variant->barcode, 'purchase_price' => $variant->purchase_price, 'sale_price' => $variant->sale_price, 'compare_price' => $variant->compare_price, 'stock_quantity' => $variant->stock_quantity, 'stock_alert_threshold' => $variant->stock_alert_threshold, 'weight' => $variant->weight, 'is_active' => (bool)$variant->is_active, 'label' => $variantLabel, 'image' => $variant->image]) }})"
                                class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-all"
                                title="Modifier tous les champs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </button>
                            <button type="button"
                                @click="deleteVariant({{ $variant->id }}, '{{ addslashes($variantLabel) }}')"
                                :disabled="deleting[{{ $variant->id }}]"
                                class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all"
                                title="Supprimer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="py-12 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
        </svg>
        <p class="text-[13px] text-gray-400 mb-1">Aucune variante pour ce produit.</p>
        <button type="button" @click="showGenerator = true; $nextTick(() => $el.closest('[x-data]').scrollIntoView({behavior:'smooth'}))"
            class="mt-2 text-[12px] text-orange-600 hover:text-orange-700 font-medium underline underline-offset-2">
            Générer la première variante
        </button>
    </div>
    @endif
</div>

{{-- DRAWER édition complète variante --}}
<div x-cloak x-show="editDrawer.open"
     class="fixed inset-0 z-[9995] flex"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="editDrawer.open = false"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-[440px] bg-white shadow-2xl flex flex-col"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         @click.stop>

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
            <div>
                <p class="text-[15px] font-bold text-gray-900" x-text="editDrawer.data.label || 'Variante'"></p>
                <p class="text-[12px] text-gray-400 mt-0.5" x-text="editDrawer.data.sku"></p>
            </div>
            <button @click="editDrawer.open = false" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:bg-gray-100 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-5 space-y-4"
             x-data="{ drawerImgPreview: null, drawerImgFile: null }"
             x-init="$watch('editDrawer.data.image', v => { drawerImgPreview = v ? '/storage/' + v : null; drawerImgFile = null; })">

            {{-- Image --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest">Image</p>
                    <span x-show="!drawerImgPreview" class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-50 text-orange-600 rounded-full text-[10px] font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        Image manquante
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative w-20 h-20 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden flex-shrink-0 cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all group"
                         @click="$refs.drawerImg.click()">
                        <img x-show="drawerImgPreview" :src="drawerImgPreview" class="w-full h-full object-cover rounded-xl">
                        <div x-show="!drawerImgPreview" class="flex flex-col items-center gap-1">
                            <svg class="w-7 h-7 text-gray-300 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-[9px] text-gray-400 group-hover:text-blue-500">Ajouter</span>
                        </div>
                        <div x-show="drawerImgPreview" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <input id="drawer-img-input" x-ref="drawerImg" type="file" accept="image/*" class="hidden"
                               @change="
                                   const f = $event.target.files[0];
                                   if (f) { drawerImgFile = f; drawerImgPreview = URL.createObjectURL(f); }
                               ">
                        <p class="text-[12px] text-gray-500 font-medium">Cliquez pour changer l'image</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">JPEG, PNG, WEBP — max 5 Mo</p>
                        <button x-show="drawerImgPreview" type="button"
                            @click="drawerImgPreview = null; drawerImgFile = null; $refs.drawerImg.value = ''; editDrawer.data.image = null"
                            class="mt-1.5 inline-flex items-center gap-1 text-[11px] text-red-500 hover:text-red-600 font-medium">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer l'image
                        </button>
                    </div>
                </div>
            </div>

            {{-- Prix --}}
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Prix</p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Achat (F)</label>
                        <input type="number" x-model="editDrawer.data.purchase_price" min="0" step="1" placeholder="—"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Vente (F)</label>
                        <input type="number" x-model="editDrawer.data.sale_price" min="0" step="1" placeholder="—"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Barré (F)</label>
                        <input type="number" x-model="editDrawer.data.compare_price" min="0" step="1" placeholder="—"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>
            </div>

            {{-- Stock --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest">Stock</p>
                    <span x-show="editDrawer.data.stock_quantity <= 0"
                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-50 text-red-600 rounded-full text-[10px] font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        Stock épuisé
                    </span>
                    <span x-show="editDrawer.data.stock_quantity > 0 && editDrawer.data.stock_quantity <= 5"
                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-600 rounded-full text-[10px] font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18 9 9 0 010-18z"/>
                        </svg>
                        Stock faible
                    </span>
                    <span x-show="editDrawer.data.stock_quantity > 5"
                        class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-50 text-green-600 rounded-full text-[10px] font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        En stock
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Quantité</label>
                        <input type="number" x-model.number="editDrawer.data.stock_quantity" min="0"
                            :class="editDrawer.data.stock_quantity <= 0 ? 'border-red-300 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                            class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Seuil alerte</label>
                        <input type="number" x-model="editDrawer.data.stock_alert_threshold" min="0" placeholder="—"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            {{-- Identifiants --}}
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Identifiants</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Barcode / EAN</label>
                        <input type="text" x-model="editDrawer.data.barcode" placeholder="ex: 3760001234567"
                            class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Poids (kg)</label>
                        <input type="number" x-model="editDrawer.data.weight" min="0" step="0.001" placeholder="—"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>
            </div>

            {{-- Statut --}}
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                <div>
                    <p class="text-[13px] font-medium text-gray-700">Variante active</p>
                    <p class="text-[11px] text-gray-400">Visible sur la boutique</p>
                </div>
                <button type="button" @click="editDrawer.data.is_active = !editDrawer.data.is_active"
                    :class="editDrawer.data.is_active ? 'bg-green-500' : 'bg-gray-200'"
                    class="relative w-10 h-6 rounded-full transition-colors flex-shrink-0">
                    <span :class="editDrawer.data.is_active ? 'translate-x-5' : 'translate-x-1'"
                          class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform"></span>
                </button>
            </div>

        </div>

        <div class="flex-shrink-0 px-5 py-4 border-t border-gray-100">
            {{-- Message succès temporaire --}}
            <div x-data="{ showSuccess: false }" x-show="showSuccess" x-transition.opacity.duration.300ms
                class="mb-3 flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-[12px] text-green-700">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">Variante enregistrée avec succès</span>
            </div>

            <div class="flex gap-3">
                <button type="button" @click="saveEditDrawer()"
                    :disabled="editDrawer.saving"
                    class="flex-1 h-9 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-sm">
                    <svg x-show="editDrawer.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg x-show="!editDrawer.saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-text="editDrawer.saving ? 'Enregistrement...' : 'Enregistrer'"></span>
                </button>
                <button type="button" @click="editDrawer.open = false"
                    :disabled="editDrawer.saving"
                    class="h-9 px-4 border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>
