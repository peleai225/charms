{{-- ── FORMULAIRE : AJOUTER UNE VARIANTE ── --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: false }">

    <button type="button" @click="open = !open"
        class="w-full flex items-center justify-between px-5 py-4 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-900">Ajouter une variante</span>
                <span class="ml-2 text-[11px] text-gray-400">Créer une variante avec attributs précis</span>
            </div>
        </div>
        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-cloak x-show="open" x-transition.opacity>
        <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" enctype="multipart/form-data" class="p-5 space-y-4 no-ajax"
              x-data="{ imgPreview: null }">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                {{-- SKU --}}
                <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" required placeholder="ex: MON-PRODUIT-ROUGE-M"
                        class="w-full h-9 px-3 text-[13px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                {{-- Stock --}}
                <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Stock initial <span class="text-red-500">*</span></label>
                    <input type="number" name="stock_quantity" value="0" min="0" required
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                {{-- Prix vente --}}
                <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Prix de vente (F)</label>
                    <input type="number" name="sale_price" min="0" step="1" placeholder="—"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Attributs --}}
            @if($attributes->count())
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Attributs</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($attributes as $attribute)
                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1">{{ $attribute->name }}</label>
                        <select name="attributes[{{ $attribute->id }}]"
                            class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Non défini —</option>
                            @foreach($attribute->values as $value)
                            <option value="{{ $value->id }}">
                                {{ $value->value }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Image variante --}}
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-2">Image (optionnel)</p>
                <div class="flex items-center gap-3">
                    <div class="w-16 h-16 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden flex-shrink-0 cursor-pointer hover:border-blue-300 transition-colors"
                         @click="$refs.variantImg.click()">
                        <img x-show="imgPreview" :src="imgPreview" class="w-full h-full object-cover rounded-lg">
                        <svg x-show="!imgPreview" class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <input x-ref="variantImg" type="file" name="image" accept="image/*" class="hidden"
                               @change="imgPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                        <p class="text-[12px] text-gray-500">Cliquez pour choisir une image</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">JPEG, PNG, WEBP — max 5 Mo</p>
                        <button x-show="imgPreview" type="button" @click="imgPreview = null; $refs.variantImg.value = ''"
                            class="mt-1 text-[11px] text-red-500 hover:text-red-600">Supprimer</button>
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t border-gray-100 flex gap-2">
                <button type="submit"
                    class="h-9 px-5 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Ajouter la variante
                </button>
                <button type="button" @click="open = false"
                    class="h-9 px-4 border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── GÉNÉRATEUR DE COMBINAISONS ── --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

    {{-- Header toggle --}}
    <button type="button" @click="showGenerator = !showGenerator"
        class="w-full flex items-center justify-between px-5 py-4 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h8m-8 4h8m6-4l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <span class="text-sm font-semibold text-gray-900">Générer des variantes</span>
                <span class="ml-2 text-[11px] text-gray-400">Combinaisons automatiques par attribut</span>
            </div>
        </div>
        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="showGenerator ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-cloak x-show="showGenerator" x-transition.opacity>
        <div class="p-5 space-y-5">

            {{-- Étape 1 : choisir les attributs --}}
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">
                    1 — Choisir les dimensions
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach($attributes as $attribute)
                    <button type="button"
                        @click="toggleAttr({{ $attribute->id }})"
                        :class="isAttrSelected({{ $attribute->id }})
                            ? 'bg-orange-600 text-white border-orange-600'
                            : 'bg-white text-gray-600 border-gray-200 hover:border-orange-300'"
                        class="h-8 px-4 text-[13px] font-medium border rounded-full transition-all flex items-center gap-2">
                        @if($attribute->type === 'color')
                            <span class="w-3 h-3 rounded-full bg-gradient-to-br from-orange-400 to-pink-400 flex-shrink-0"></span>
                        @endif
                        {{ $attribute->name }}
                        <span x-show="isAttrSelected({{ $attribute->id }})"
                              class="text-[10px] opacity-70"
                              x-text="'(' + (selectedValues[{{ $attribute->id }}] || []).length + ')'"></span>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Étape 2 : valeurs par attribut sélectionné --}}
            <template x-for="attrId in selectedAttrs" :key="attrId">
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-2.5">
                        2 — <span x-text="attrById(attrId)?.name"></span>
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="val in (attrById(attrId)?.values || [])" :key="val.id">
                            <button type="button"
                                @click="toggleValue(attrId, val.id)"
                                :class="isValueSelected(attrId, val.id)
                                    ? 'ring-2 ring-orange-500 ring-offset-1'
                                    : 'ring-1 ring-gray-200 hover:ring-orange-300'"
                                class="relative transition-all rounded-lg overflow-hidden">

                                {{-- Swatch couleur --}}
                                <template x-if="attrById(attrId)?.type === 'color' && val.color_code">
                                    <span class="flex items-center gap-2 pl-1.5 pr-3 py-1.5 text-[12px] font-medium text-gray-700">
                                        <span class="w-5 h-5 rounded flex-shrink-0 border border-black/10"
                                              :style="'background:' + val.color_code"></span>
                                        <span x-text="val.value"></span>
                                        <svg x-show="isValueSelected(attrId, val.id)" class="w-3.5 h-3.5 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                </template>

                                {{-- Chip texte --}}
                                <template x-if="attrById(attrId)?.type !== 'color' || !val.color_code">
                                    <span class="flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-medium"
                                          :class="isValueSelected(attrId, val.id) ? 'text-orange-700 bg-orange-50' : 'text-gray-700 bg-white'">
                                        <span x-text="val.value"></span>
                                        <svg x-show="isValueSelected(attrId, val.id)" class="w-3 h-3 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Bouton générer --}}
            <div x-show="selectedAttrs.length > 0" class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="button" @click="generate()"
                    :disabled="!canGenerate()"
                    class="h-9 px-5 bg-gray-900 text-white text-[13px] font-semibold rounded-lg hover:bg-gray-800 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Générer
                    <span x-show="canGenerate()" class="text-[11px] text-gray-300"
                          x-text="'(' + selectedAttrs.reduce((t, aid) => t * (selectedValues[aid] || []).length, 1) + ' combinaisons)'"></span>
                </button>
                <template x-if="!canGenerate() && selectedAttrs.length > 0">
                    <p class="text-[12px] text-gray-400">Sélectionnez au moins une valeur par dimension</p>
                </template>
            </div>

            {{-- Étape 3 : tableau des combinaisons générées --}}
            <template x-if="generatedRows.length > 0">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest">
                            3 — Ajuster et créer
                            <span class="normal-case font-normal text-gray-400 ml-1"
                                  x-text="generatedRows.filter(r => !r.remove).length + ' variante(s)'"></span>
                        </p>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] text-gray-400">Stock par défaut :</span>
                            <input type="number" min="0" value="0"
                                @change="generatedRows.forEach(r => r.stock = parseInt($event.target.value) || 0)"
                                class="w-16 h-7 px-2 text-[12px] text-center border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                    </div>

                    <div class="border border-gray-200 rounded-xl overflow-x-auto">
                        <table class="w-full text-[13px] min-w-[900px]">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Variante</th>
                                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-36">SKU</th>
                                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-32">Barcode</th>
                                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-20">Stock</th>
                                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-28">Achat (F)</th>
                                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-28">Vente (F)</th>
                                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-28">Barré (F)</th>
                                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide w-24">Poids (kg)</th>
                                    <th class="px-3 py-2.5 w-8"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(row, i) in generatedRows" :key="i">
                                    <tr :class="row.remove ? 'opacity-30 line-through' : ''" class="transition-opacity">
                                        <td class="px-3 py-2.5">
                                            <div class="flex items-center gap-2">
                                                <template x-if="row.colors.length > 0">
                                                    <div class="flex -space-x-1 flex-shrink-0">
                                                        <template x-for="c in row.colors" :key="c">
                                                            <span class="w-4 h-4 rounded-full border border-white"
                                                                  :style="'background:' + c"></span>
                                                        </template>
                                                    </div>
                                                </template>
                                                <span class="font-medium text-gray-800" x-text="row.label"></span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <div class="relative"
                                                 x-data="{
                                                     isDuplicate: false,
                                                     init() {
                                                         this.$watch('row.sku', () => {
                                                             const skus = $root.generatedRows.map(r => r.sku.trim().toUpperCase()).filter(s => s);
                                                             this.isDuplicate = skus.filter(s => s === row.sku.trim().toUpperCase()).length > 1;
                                                         });
                                                     }
                                                 }">
                                                <input type="text" x-model="row.sku"
                                                    :disabled="row.remove"
                                                    :class="isDuplicate && !row.remove ? 'border-red-300 bg-red-50 focus:ring-red-500' : 'border-gray-200 focus:ring-blue-500'"
                                                    class="w-full h-7 px-2 text-[12px] font-mono border rounded-lg focus:outline-none focus:ring-1 disabled:bg-gray-50 transition-colors">
                                                <div x-show="isDuplicate && !row.remove" class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="text" x-model="row.barcode" placeholder="—"
                                                :disabled="row.remove"
                                                class="w-full h-7 px-2 text-[12px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:bg-gray-50">
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="number" x-model.number="row.stock" min="0"
                                                :disabled="row.remove"
                                                class="w-full h-7 px-2 text-[12px] text-center border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:bg-gray-50">
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="number" x-model="row.purchase_price" min="0" step="1" placeholder="—"
                                                :disabled="row.remove"
                                                class="w-full h-7 px-2 text-[12px] text-center border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:bg-gray-50">
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="number" x-model="row.price" min="0" step="1" placeholder="—"
                                                :disabled="row.remove"
                                                class="w-full h-7 px-2 text-[12px] text-center border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:bg-gray-50">
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="number" x-model="row.compare_price" min="0" step="1" placeholder="—"
                                                :disabled="row.remove"
                                                class="w-full h-7 px-2 text-[12px] text-center border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:bg-gray-50">
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="number" x-model="row.weight" min="0" step="0.001" placeholder="—"
                                                :disabled="row.remove"
                                                class="w-full h-7 px-2 text-[12px] text-center border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:bg-gray-50">
                                        </td>
                                        <td class="px-3 py-2.5 text-center">
                                            <button type="button" @click="row.remove = !row.remove"
                                                :class="row.remove ? 'text-orange-500 hover:text-orange-700' : 'text-gray-300 hover:text-red-500'"
                                                class="transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          :d="row.remove ? 'M4 4l16 16M4 20L20 4' : 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Messages validation --}}
                    <div class="mt-3 space-y-2">
                        {{-- Message d'erreur bulk --}}
                        <template x-if="bulkError">
                            <div class="flex items-start gap-2 px-3 py-2.5 bg-red-50 border border-red-200 rounded-lg text-[12px] text-red-700 animate-pulse">
                                <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <span x-text="bulkError"></span>
                            </div>
                        </template>

                        {{-- Alerte SKU dupliqués --}}
                        <template x-if="generatedRows.filter(r => !r.remove).length > 0">
                            <div x-data="{
                                hasDuplicates: false,
                                init() {
                                    this.$watch('$root.generatedRows', () => {
                                        const skus = $root.generatedRows.filter(r => !r.remove).map(r => r.sku.trim().toUpperCase()).filter(s => s);
                                        this.hasDuplicates = skus.length !== new Set(skus).size;
                                    }, { deep: true });
                                }
                            }">
                                <div x-show="hasDuplicates" x-transition.opacity
                                    class="flex items-start gap-2 px-3 py-2.5 bg-orange-50 border border-orange-200 rounded-lg text-[12px] text-orange-700">
                                    <svg class="w-4 h-4 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18 9 9 0 010-18z"/>
                                    </svg>
                                    <span class="font-medium">Attention : certains SKU sont dupliqués (point rouge). Corrigez-les avant de créer.</span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center gap-3 mt-4">
                        <button type="button" @click="submitBulk()"
                            :disabled="bulkSubmitting || generatedRows.filter(r => !r.remove).length === 0"
                            class="h-9 px-6 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-sm">
                            <svg x-show="bulkSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <svg x-show="!bulkSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span x-text="bulkSubmitting ? 'Création en cours...' : 'Créer ' + generatedRows.filter(r => !r.remove).length + ' variante(s)'"></span>
                        </button>
                        <button type="button" @click="generatedRows = []; bulkError = null"
                            :disabled="bulkSubmitting"
                            class="h-9 px-4 border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            Réinitialiser
                        </button>
                    </div>
                </div>
            </template>

        </div>
    </div>

</div>
