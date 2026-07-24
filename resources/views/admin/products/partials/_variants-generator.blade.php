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
                                            <input type="text" x-model="row.sku"
                                                :disabled="row.remove"
                                                class="w-full h-7 px-2 text-[12px] font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-orange-500 disabled:bg-gray-50">
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

                    <div class="flex items-center gap-3 mt-4">
                        <button type="button" @click="submitBulk()"
                            :disabled="bulkSubmitting || generatedRows.filter(r => !r.remove).length === 0"
                            class="h-9 px-6 bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg x-show="bulkSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span x-text="bulkSubmitting ? 'Création...' : 'Créer ' + generatedRows.filter(r => !r.remove).length + ' variante(s)'"></span>
                        </button>
                        <button type="button" @click="generatedRows = []"
                            class="h-9 px-4 border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition">
                            Réinitialiser
                        </button>
                    </div>
                </div>
            </template>

        </div>
    </div>

</div>
