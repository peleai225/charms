<script setup>
import { computed } from 'vue'

// Choix d'une déclinaison (taille, pointure…) et de ses valeurs.
const props = defineProps({
    attributes: { type: Array, required: true }, // [{ id, name, values: [{ id, value }] }]
    disabled: Boolean,
    label: { type: String, default: 'Tailles / déclinaison' },
    emptyLabel: { type: String, default: 'Aucune (taille unique)' },
})
const attributeId = defineModel('attributeId', { default: '' })
const valueIds = defineModel('valueIds', { default: () => [] })
const emit = defineEmits(['touched'])

const attribute = computed(() => props.attributes.find((a) => a.id === Number(attributeId.value)))

function onAttributeChange() {
    valueIds.value = []
    emit('touched')
}

function toggle(id) {
    valueIds.value = valueIds.value.includes(id) ? valueIds.value.filter((v) => v !== id) : [...valueIds.value, id]
    emit('touched')
}

function selectAll(all) {
    valueIds.value = all ? attribute.value.values.map((v) => v.id) : []
    emit('touched')
}

const inputClass = 'w-full h-11 px-3 text-[13px] border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500'
</script>

<template>
    <div class="space-y-2">
        <div class="flex flex-wrap items-end gap-3">
            <div class="w-full sm:w-56">
                <label class="block text-xs font-medium text-gray-700 mb-1">{{ label }}</label>
                <select v-model="attributeId" :class="inputClass" :disabled="disabled" @change="onAttributeChange">
                    <option value="">{{ emptyLabel }}</option>
                    <option v-for="a in attributes" :key="a.id" :value="a.id">{{ a.name }}</option>
                </select>
            </div>
            <div v-if="attribute" class="flex gap-2">
                <button type="button" class="h-11 px-3 text-xs text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50" :disabled="disabled" @click="selectAll(true)">Tout</button>
                <button type="button" class="h-11 px-3 text-xs text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50" :disabled="disabled" @click="selectAll(false)">Aucune</button>
            </div>
        </div>
        <div v-if="attribute" class="flex flex-wrap gap-2">
            <button v-for="v in attribute.values" :key="v.id" type="button" :disabled="disabled"
                :aria-pressed="valueIds.includes(v.id)"
                class="min-w-[44px] h-11 px-3 text-sm rounded-lg border transition-colors"
                :class="valueIds.includes(v.id) ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                @click="toggle(v.id)">{{ v.value }}</button>
        </div>
    </div>
</template>
