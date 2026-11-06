<script setup>
import { computed, watchEffect } from 'vue'
import VariantStockGrid from './VariantStockGrid.vue'
import SizePicker from './SizePicker.vue'
import { isVariantMode, variantRows, photoColorKey, applyCategoryOptions } from './photoImport.js'

// Une fiche = un produit (une ou plusieurs photos du même article).
const props = defineProps({
    card: { type: Object, required: true },
    categories: { type: Array, required: true },
    colors: { type: Array, required: true },
    optionAttributes: { type: Array, required: true },
    categoryOptions: { type: Object, required: true },
    aiConfigured: Boolean,
    useAi: Boolean,
})
const emit = defineEmits(['remove', 'separate', 'reanalyze'])

const card = props.card
const busy = computed(() => card.status === 'creating')
const analyzing = computed(() => card.photos.some((p) => ['pending', 'analyzing'].includes(p.status)))
const waitingForText = computed(() => props.useAi && !card.autofilled && ['pending', 'analyzing'].includes(card.photos[0]?.status))
const variantMode = computed(() => isVariantMode(card))
const rows = computed(() => variantRows(card, props.colors, props.optionAttributes))

// Stock commun de l'arrivage : chaque variante qui apparaît (couleur repérée, taille cochée) le reçoit
watchEffect(() => {
    if (card.stockDefault === '' || card.status === 'created') return
    rows.value.forEach((r) => {
        if (card.stock[r.key] === undefined) card.stock[r.key] = card.stockDefault
    })
})

const suggestedFromCategory = computed(() => {
    const s = props.categoryOptions[card.fields.category_id]
    return !card.optionsTouched && s && s.attribute_id === Number(card.optionAttributeId) && card.optionValueIds.length > 0
})
const categoryName = computed(() => props.categories.find((c) => c.id === Number(card.fields.category_id))?.name ?? '')

const swatch = (photo) => {
    if (photo.colorPick === 'new') return photo.newColorHex
    return props.colors.find((c) => c.id === Number(photo.colorPick))?.color_code || null
}

const inputClass = 'w-full h-11 px-3 text-[13px] border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500'
</script>

<template>
    <div class="rounded-xl border bg-white p-4 shadow-sm space-y-4"
        :class="card.missing ? 'border-red-300' : card.status === 'created' ? 'border-green-300' : card.selected ? 'border-blue-400' : 'border-gray-200'">

        <!-- Photos -->
        <div class="flex items-start gap-3">
            <input v-if="card.status === 'editing'" v-model="card.selected" type="checkbox"
                :aria-label="`Sélectionner ${card.fields.name || 'cette fiche'} pour la regrouper`"
                class="mt-1 w-5 h-5 rounded border-gray-300 flex-shrink-0">
            <div class="flex-1 flex gap-3 overflow-x-auto pb-1">
                <div v-for="photo in card.photos" :key="photo.id" class="w-32 flex-shrink-0 space-y-1.5">
                    <div class="relative">
                        <img :src="photo.preview" :alt="card.fields.name" class="w-32 h-32 object-cover rounded-lg border border-gray-100">
                        <span v-if="['pending', 'analyzing'].includes(photo.status)"
                            class="absolute inset-x-1 bottom-1 rounded bg-white/90 px-1.5 py-0.5 text-[11px] text-gray-600 text-center">
                            {{ photo.status === 'analyzing' ? 'Analyse…' : 'En attente…' }}
                        </span>
                    </div>
                    <template v-if="variantMode && card.status !== 'created'">
                        <div class="flex items-center gap-1.5">
                            <span class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0" :style="{ background: swatch(photo) || '#fff' }"></span>
                            <select v-model="photo.colorPick" :disabled="busy" :aria-label="'Couleur de la photo'"
                                class="w-full h-11 px-1.5 text-xs border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="card.photos.length > 1 && !photoColorKey(photo, colors) ? 'border-red-300' : 'border-gray-200'">
                                <option value="">Couleur ?</option>
                                <option v-for="c in colors" :key="c.id" :value="c.id">{{ c.value }}</option>
                                <option value="new">Nouvelle couleur…</option>
                            </select>
                        </div>
                        <div v-if="photo.colorPick === 'new'" class="flex items-center gap-1.5">
                            <input v-model="photo.newColorHex" type="color" :disabled="busy" aria-label="Teinte de la nouvelle couleur"
                                class="w-11 h-11 rounded-lg border border-gray-200 flex-shrink-0">
                            <input v-model="photo.newColorName" type="text" maxlength="50" placeholder="Nom" :disabled="busy"
                                aria-label="Nom de la nouvelle couleur"
                                class="w-full h-11 px-2 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </template>
                    <p v-if="photo.error" class="text-[11px] text-amber-700">{{ photo.error }}</p>
                    <button v-if="card.photos.length > 1 && card.status === 'editing'" type="button" :disabled="busy"
                        class="w-full h-11 text-xs text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50"
                        @click="emit('separate', photo)">Séparer</button>
                </div>
            </div>
        </div>

        <!-- Créé -->
        <div v-if="card.status === 'created'" class="space-y-1">
            <p class="text-sm font-semibold text-green-700">
                ✓ {{ card.fields.name }} créé<span v-if="card.variantCount"> avec {{ card.variantCount }} variante(s)</span>
            </p>
            <a :href="card.editUrl" class="text-sm text-blue-600 hover:underline">Ouvrir la fiche →</a>
        </div>

        <!-- Texte en cours de rédaction par l'IA -->
        <div v-else-if="waitingForText" class="space-y-3 animate-pulse">
            <p class="text-sm text-gray-500">L'IA prépare la fiche…</p>
            <div class="h-11 bg-gray-100 rounded-lg"></div>
            <div class="h-16 bg-gray-100 rounded-lg"></div>
        </div>

        <!-- Formulaire -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Nom *</label>
                <input v-model="card.fields.name" type="text" :class="inputClass" :disabled="busy">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Accroche</label>
                <input v-model="card.fields.short_description" type="text" maxlength="500" :class="inputClass" :disabled="busy">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                <textarea v-model="card.fields.description" rows="3" :disabled="busy"
                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Catégorie</label>
                <select v-model="card.fields.category_id" :class="inputClass" :disabled="busy" @change="applyCategoryOptions(card, categoryOptions)">
                    <option value="">— Sans catégorie —</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Prix de vente (F CFA) *</label>
                <input v-model="card.fields.sale_price" type="number" min="0" step="1" :class="inputClass" :disabled="busy">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Prix d'achat (F CFA)</label>
                <input v-model="card.fields.purchase_price" type="number" min="0" step="1" :class="inputClass" :disabled="busy">
            </div>

            <!-- Déclinaison : tailles, pointures… -->
            <div v-if="optionAttributes.length" class="md:col-span-2 space-y-2">
                <SizePicker v-model:attribute-id="card.optionAttributeId" v-model:value-ids="card.optionValueIds"
                    :attributes="optionAttributes" :disabled="busy" @touched="card.optionsTouched = true" />
                <p v-if="suggestedFromCategory" class="text-xs text-gray-500">
                    Pré-coché d'après vos autres produits « {{ categoryName }} ». Décochez ce que vous n'avez pas.
                </p>
            </div>

            <!-- Stock -->
            <div v-if="variantMode" class="md:col-span-2">
                <VariantStockGrid v-if="rows.length" :rows="rows" :stock="card.stock" :disabled="busy" />
                <p v-else class="text-xs text-red-600">Choisissez la couleur de chaque photo pour obtenir les variantes.</p>
            </div>
            <div v-else>
                <label class="block text-xs font-medium text-gray-700 mb-1">Stock</label>
                <input v-model="card.fields.stock_quantity" type="number" min="0" step="1" :class="inputClass" :disabled="busy">
            </div>

            <div class="md:col-span-2 flex flex-wrap items-center gap-2">
                <button v-if="aiConfigured" type="button" :disabled="busy || analyzing"
                    class="h-11 px-3 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40"
                    @click="emit('reanalyze')">Relancer l'IA</button>
                <button type="button" :disabled="busy"
                    class="h-11 px-3 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 disabled:opacity-40"
                    @click="emit('remove')">Retirer</button>
                <span v-if="busy" class="text-sm text-gray-500">Création…</span>
            </div>
            <p v-if="card.error" class="md:col-span-2 text-xs text-red-600">{{ card.error }}</p>
        </div>
    </div>
</template>
