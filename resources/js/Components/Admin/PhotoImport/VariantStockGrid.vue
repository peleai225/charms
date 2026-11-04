<script setup>
import { ref, computed } from 'vue'

// Grille de stock : une ligne par couleur, une colonne par taille.
const props = defineProps({
    rows: { type: Array, required: true }, // [{ key, color, value }]
    stock: { type: Object, required: true }, // { [row.key]: quantité }
    disabled: Boolean,
})

const fillValue = ref('')

const colorLines = computed(() => {
    const lines = new Map()
    for (const row of props.rows) {
        const id = row.color?.key ?? '-'
        if (!lines.has(id)) lines.set(id, { color: row.color, cells: [] })
        lines.get(id).cells.push(row)
    }
    return [...lines.values()]
})

const columns = computed(() => colorLines.value[0]?.cells.map((c) => c.value) ?? [])
const hasSizes = computed(() => columns.value.some(Boolean))
const hasColors = computed(() => colorLines.value.some((l) => l.color))
const total = computed(() => props.rows.reduce((t, r) => t + (parseInt(props.stock[r.key]) || 0), 0))

function fillAll() {
    if (fillValue.value === '') return
    props.rows.forEach((r) => { props.stock[r.key] = fillValue.value })
}
</script>

<template>
    <div class="space-y-2">
        <div class="flex flex-wrap items-center gap-2">
            <p class="text-xs font-medium text-gray-700 mr-auto">
                Stock par variante · {{ rows.length }} variante(s) · total {{ total }}
            </p>
            <input v-model="fillValue" type="number" min="0" step="1" placeholder="Qté" :disabled="disabled"
                aria-label="Quantité à mettre partout"
                class="w-20 h-11 px-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="button" :disabled="disabled || fillValue === ''"
                class="h-11 px-3 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40"
                @click="fillAll">Mettre partout</button>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th v-if="hasColors" class="px-3 py-2 text-left font-medium">Couleur</th>
                        <th v-for="(col, i) in columns" :key="col?.id ?? i" class="px-2 py-2 text-center font-medium whitespace-nowrap">
                            {{ hasSizes ? col.value : 'Stock' }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="line in colorLines" :key="line.color?.key ?? '-'">
                        <td v-if="hasColors" class="px-3 py-1.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-2">
                                <span class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0" :style="{ background: line.color?.hex || '#fff' }"></span>
                                {{ line.color?.label || '—' }}
                            </span>
                        </td>
                        <td v-for="cell in line.cells" :key="cell.key" class="px-2 py-1.5 text-center">
                            <input v-model="stock[cell.key]" type="number" min="0" step="1" placeholder="0" :disabled="disabled"
                                :aria-label="`Stock ${[cell.color?.label, cell.value?.value].filter(Boolean).join(' ')}`"
                                class="w-16 h-11 px-2 text-center text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500">Case vide = 0 en stock. Le prix de vente s'applique à toutes les variantes (modifiable ensuite dans la fiche).</p>
    </div>
</template>
