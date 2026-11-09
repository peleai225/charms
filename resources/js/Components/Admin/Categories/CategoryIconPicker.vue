<script setup>
import CategoryIcon from '@/Components/Storefront/CategoryIcon.vue'

// Choix de l'icône d'une catégorie dans la liste fermée du serveur (App\Support\CategoryIcons).
// Valeur vide = pas d'icône (la boutique affiche alors une icône neutre).
const model = defineModel({ type: String, default: '' })

defineProps({
    options: { type: Array, default: () => [] }, // [{ key, label }]
    name:    { type: String, required: true },   // nom du groupe de boutons radio (unique par formulaire)
})
</script>

<template>
    <fieldset>
        <legend class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Icône</legend>
        <p class="text-[12px] text-gray-500 mb-2">
            Affichée dans le menu des catégories et, sans image, dans « Nos univers populaires ».
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 max-h-52 overflow-y-auto p-0.5 -m-0.5">
            <label v-for="opt in [{ key: '', label: 'Aucune' }, ...options]" :key="opt.key || 'none'"
                class="min-h-11 px-2.5 flex items-center gap-2 rounded-lg border border-gray-200 cursor-pointer text-gray-700 hover:bg-gray-50
                       has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:text-primary-800
                       has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-primary-500">
                <input v-model="model" type="radio" :name="name" :value="opt.key" class="sr-only">
                <CategoryIcon v-if="opt.key" :icon="opt.key" class="w-4 h-4 flex-shrink-0" />
                <span v-else class="w-4 h-4 flex-shrink-0 rounded border border-dashed border-gray-300" aria-hidden="true"></span>
                <span class="text-[12px] leading-tight">{{ opt.label }}</span>
            </label>
        </div>
    </fieldset>
</template>
