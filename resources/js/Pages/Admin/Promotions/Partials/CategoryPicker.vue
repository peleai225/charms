<script setup>
import { computed } from 'vue'

const props = defineProps({
    categories: { type: Array, required: true },
    modelValue: { type: Array, default: () => [] },
    includeDescendants: { type: Boolean, default: true },
    error: { type: String, default: null },
})

const emit = defineEmits(['update:modelValue', 'update:includeDescendants'])

/**
 * Aplatit l'arbre parent_id en une liste ordonnée, avec un niveau
 * d'indentation par catégorie — plus lisible qu'un arbre replié pour une
 * sélection multiple, et sans état d'ouverture à gérer.
 */
const flattened = computed(() => {
    const byParent = new Map()

    for (const category of props.categories) {
        const key = category.parent_id ?? 0
        if (!byParent.has(key)) byParent.set(key, [])
        byParent.get(key).push(category)
    }

    const rows = []

    const walk = (parentId, depth) => {
        for (const category of byParent.get(parentId) ?? []) {
            rows.push({ ...category, depth })
            walk(category.id, depth + 1)
        }
    }

    walk(0, 0)

    // Une catégorie dont le parent est inactif (donc absent de la liste) serait
    // perdue : on la rattache à la racine plutôt que de la masquer.
    const seen = new Set(rows.map(r => r.id))
    for (const category of props.categories) {
        if (!seen.has(category.id)) rows.push({ ...category, depth: 0 })
    }

    return rows
})

const isChecked = (id) => props.modelValue.includes(id)

function toggle(id) {
    const next = isChecked(id)
        ? props.modelValue.filter(existing => existing !== id)
        : [...props.modelValue, id]

    emit('update:modelValue', next)
}
</script>

<template>
    <div>
        <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Catégories ciblées</label>

        <div class="border border-gray-200 rounded-lg divide-y divide-gray-50 max-h-72 overflow-y-auto"
            :class="error ? 'border-red-300' : ''">
            <p v-if="flattened.length === 0" class="px-3 py-6 text-center text-[13px] text-gray-400">
                Aucune catégorie active. Créez-en une, ou ciblez des produits nommément.
            </p>

            <label v-for="category in flattened" :key="category.id"
                class="flex items-center gap-2.5 px-3 py-2.5 hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" :checked="isChecked(category.id)" @change="toggle(category.id)"
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-[13px] text-gray-800" :style="{ paddingLeft: category.depth * 16 + 'px' }">
                    {{ category.name }}
                </span>
            </label>
        </div>

        <p v-if="error" class="mt-1 text-[12px] text-red-600">{{ error }}</p>

        <label class="mt-3 flex items-start gap-2.5 cursor-pointer">
            <input type="checkbox" :checked="includeDescendants"
                @change="emit('update:includeDescendants', $event.target.checked)"
                class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="text-[13px] text-gray-700">
                Inclure les sous-catégories
                <span class="block text-[12px] text-gray-500">
                    Coché, une offre sur « Vêtements » capte aussi les T-shirts et les Polos.
                </span>
            </span>
        </label>
    </div>
</template>
