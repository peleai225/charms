<script setup>
const props = defineProps({
    columns:    { type: Array,   required: true },
    selectable: { type: Boolean, default: false },
    allSelected:{ type: Boolean, default: false },
    hasActions: { type: Boolean, default: false },
})
const emit = defineEmits(['toggle-all', 'sort'])
</script>

<template>
    <thead class="bg-gray-50 border-b border-gray-100">
        <tr>
            <th v-if="selectable" class="w-10 px-4 py-3">
                <input
                    type="checkbox"
                    :checked="allSelected"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    @change="emit('toggle-all', $event.target.checked)"
                />
            </th>
            <th
                v-for="col in columns"
                :key="col.key"
                :class="[
                    'px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide',
                    col.align === 'right'  ? 'text-right'  : '',
                    col.align === 'center' ? 'text-center' : 'text-left',
                    col.width || '',
                    col.sortable ? 'cursor-pointer select-none hover:text-gray-700' : '',
                ]"
                @click="col.sortable && emit('sort', col.key)"
            >
                {{ col.label }}
            </th>
            <th v-if="hasActions" class="w-12 px-4 py-3" />
        </tr>
    </thead>
</template>
