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
                <tr id="variant-row-{{ $variant->id }}" class="hover:bg-gray-50/60 transition-colors group">

                    {{-- Identité --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            @if($variant->image)
                                <img src="{{ asset('storage/' . $variant->image) }}" class="w-9 h-9 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                            @elseif($vColor && $vColor->color_code)
                                <span class="w-9 h-9 rounded-lg border border-gray-200 flex-shrink-0 inline-block" style="background:{{ $vColor->color_code }}"></span>
                            @else
                                <span class="w-9 h-9 rounded-lg bg-gray-100 border border-gray-200 flex-shrink-0 inline-block"></span>
                            @endif
                            <div class="flex flex-wrap items-center gap-1 min-w-0">
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
                        x-data="{ editing: false, val: {{ $variant->stock_quantity }} }">
                        <template x-if="!editing">
                            <div class="flex items-center justify-center gap-1.5">
                                <span id="stock-badge-{{ $variant->id }}"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                        {{ $variant->stock_quantity <= 0 ? 'bg-red-100 text-red-700' : ($variant->stock_quantity <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">
                                    {{ $variant->stock_quantity <= 0 ? 'Rupture' : $variant->stock_quantity . ' pcs' }}
                                </span>
                                <button type="button" @click="editing = true"
                                    class="opacity-0 group-hover:opacity-100 w-5 h-5 flex items-center justify-center text-gray-400 hover:text-orange-500 transition-opacity">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="editing">
                            <div class="flex items-center justify-center gap-1">
                                <input type="number" x-model.number="val" min="0"
                                    class="w-16 h-7 px-2 text-[12px] border border-orange-300 rounded focus:outline-none focus:ring-1 focus:ring-orange-500 text-center"
                                    @keydown.enter.prevent="patchVariant({{ $variant->id }}, { stock_quantity: val }); editing = false"
                                    @keydown.escape="editing = false"
                                    x-init="$nextTick(() => $el.focus())">
                                <button type="button" @click="patchVariant({{ $variant->id }}, { stock_quantity: val }); editing = false"
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
                    <td class="px-4 py-3 text-center">
                        <button type="button"
                            id="active-badge-{{ $variant->id }}"
                            @click="patchVariant({{ $variant->id }}, { is_active: document.getElementById('active-badge-{{ $variant->id }}').textContent.trim() !== 'Active' })"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold cursor-pointer hover:opacity-75 transition-opacity
                                {{ $variant->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $variant->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </td>

                    {{-- Actions : édition complète + suppression --}}
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button"
                                @click="openEditDrawer({{ json_encode(['id' => $variant->id, 'sku' => $variant->sku, 'barcode' => $variant->barcode, 'purchase_price' => $variant->purchase_price, 'sale_price' => $variant->sale_price, 'compare_price' => $variant->compare_price, 'stock_quantity' => $variant->stock_quantity, 'stock_alert_threshold' => $variant->stock_alert_threshold, 'weight' => $variant->weight, 'is_active' => (bool)$variant->is_active, 'label' => $variantLabel]) }})"
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

        <div class="flex-1 overflow-y-auto p-5 space-y-4">

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
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Stock</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Quantité</label>
                        <input type="number" x-model.number="editDrawer.data.stock_quantity" min="0"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Seuil alerte</label>
                        <input type="number" x-model="editDrawer.data.stock_alert_threshold" min="0" placeholder="—"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
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

        <div class="flex-shrink-0 px-5 py-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="saveEditDrawer()"
                :disabled="editDrawer.saving"
                class="flex-1 h-9 bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition disabled:opacity-50 flex items-center justify-center gap-2">
                <svg x-show="editDrawer.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span x-text="editDrawer.saving ? 'Enregistrement...' : 'Enregistrer'"></span>
            </button>
            <button type="button" @click="editDrawer.open = false"
                class="h-9 px-4 border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition">
                Annuler
            </button>
        </div>
    </div>
</div>
