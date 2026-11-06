<script setup>
import SizePicker from './SizePicker.vue'

// Réglages saisis une seule fois pour tout l'arrivage : prix, stock, tailles.
defineProps({
    defaults: { type: Object, required: true },
    optionAttributes: { type: Array, required: true },
    disabled: Boolean,
})

const inputClass = 'w-full h-11 px-3 text-[13px] border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500'
</script>

<template>
    <section class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
        <div>
            <h2 class="text-sm font-semibold text-gray-900">Réglages communs à l'arrivage</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Remplissez une seule fois : toutes les fiches les reprennent, y compris les prochaines. Une fiche reste modifiable à part.
            </p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <label>
                <span class="block text-xs font-medium text-gray-700 mb-1">Prix de vente (F CFA)</span>
                <input v-model="defaults.sale_price" type="number" min="0" step="1" :class="inputClass" :disabled="disabled">
            </label>
            <label>
                <span class="block text-xs font-medium text-gray-700 mb-1">Prix d'achat (F CFA)</span>
                <input v-model="defaults.purchase_price" type="number" min="0" step="1" :class="inputClass" :disabled="disabled">
            </label>
            <label>
                <span class="block text-xs font-medium text-gray-700 mb-1">Stock par variante</span>
                <input v-model="defaults.stock" type="number" min="0" step="1" placeholder="Ex : 5" :class="inputClass" :disabled="disabled">
            </label>
        </div>
        <SizePicker v-if="optionAttributes.length"
            v-model:attribute-id="defaults.optionAttributeId" v-model:value-ids="defaults.optionValueIds"
            :attributes="optionAttributes" :disabled="disabled" label="Tailles de tout l'arrivage" empty-label="Selon chaque fiche" />
    </section>
</template>
