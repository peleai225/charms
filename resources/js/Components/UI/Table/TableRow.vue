<script setup>
const props = defineProps({
    row:        { type: Object,  required: true },
    columns:    { type: Array,   required: true },
    selectable: { type: Boolean, default: false },
    selected:   { type: Boolean, default: false },
    hasActions: { type: Boolean, default: false },
    index:      { type: Number,  required: true },
})
const emit = defineEmits(['toggle'])

function getNestedValue(obj, key) {
    return key.split('.').reduce((o, k) => o?.[k], obj)
}
</script>

<template>
    <tr :class="['border-b border-gray-50 hover:bg-gray-50/50 transition-colors', selected ? 'bg-blue-50/30' : '']">
        <td v-if="selectable" class="w-10 px-4 py-3">
            <input
                type="checkbox"
                :checked="selected"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                @change="emit('toggle', row)"
            />
        </td>
        <td
            v-for="col in columns"
            :key="col.key"
            :class="['px-4 py-3 text-sm text-gray-700', col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : '']"
        >
            <component
                v-if="col.component"
                :is="col.component"
                v-bind="{ [col.key]: getNestedValue(row, col.key) }"
            >
                {{ getNestedValue(row, col.key) }}
            </component>
            <template v-else>
                {{ col.format ? col.format(getNestedValue(row, col.key), row) : getNestedValue(row, col.key) }}
            </template>
        </td>
        <td v-if="hasActions" class="w-12 px-4 py-3 text-right">
            <slot name="actions" :row="row" />
        </td>
    </tr>
</template>
