<script setup>
import { computed } from 'vue'
import TableHeader from './TableHeader.vue'
import TableRow from './TableRow.vue'
import EmptyState from '@/Components/UI/EmptyState.vue'
import Skeleton from '@/Components/UI/Skeleton.vue'

const props = defineProps({
    data:             { type: Array,   default: () => [] },
    columns:          { type: Array,   required: true },
    loading:          { type: Boolean, default: false },
    selected:         { type: Array,   default: () => [] },
    emptyTitle:       { type: String,  default: 'Aucun résultat' },
    emptyDescription: { type: String,  default: '' },
    emptyIcon:        { type: String,  default: 'inbox' },
})

const emit = defineEmits(['update:selected'])

const slots = defineSlots()

const selectable  = computed(() => props.selected !== undefined && props.selected !== null)
const hasActions  = computed(() => !!slots.actions)
const allSelected = computed(() => props.data.length > 0 && props.selected.length === props.data.length)

function toggleAll(checked) {
    emit('update:selected', checked ? [...props.data] : [])
}

function toggleRow(row) {
    const idx = props.selected.findIndex(r => r === row)
    const next = [...props.selected]
    idx === -1 ? next.push(row) : next.splice(idx, 1)
    emit('update:selected', next)
}

function isSelected(row) {
    return props.selected.includes(row)
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white">
        <div
            v-if="selectable && selected.length > 0 && $slots['bulk-actions']"
            class="flex items-center gap-3 px-4 py-3 bg-blue-50 border-b border-blue-100"
        >
            <span class="text-sm font-medium text-blue-700">{{ selected.length }} sélectionné(s)</span>
            <slot name="bulk-actions" :selected="selected" />
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <TableHeader
                    :columns="columns"
                    :selectable="selectable"
                    :all-selected="allSelected"
                    :has-actions="hasActions"
                    @toggle-all="toggleAll"
                />
                <tbody>
                    <template v-if="loading">
                        <tr v-for="i in 5" :key="i" class="border-b border-gray-50">
                            <td v-if="selectable" class="px-4 py-3"><Skeleton class="h-4 w-4 rounded" /></td>
                            <td v-for="col in columns" :key="col.key" class="px-4 py-3">
                                <Skeleton class="h-4 rounded" :class="col.width || 'w-full'" />
                            </td>
                            <td v-if="hasActions" class="px-4 py-3"><Skeleton class="h-6 w-6 rounded ml-auto" /></td>
                        </tr>
                    </template>

                    <tr v-else-if="data.length === 0">
                        <td :colspan="columns.length + (selectable ? 1 : 0) + (hasActions ? 1 : 0)">
                            <EmptyState :icon="emptyIcon" :title="emptyTitle" :description="emptyDescription">
                                <slot name="empty" />
                            </EmptyState>
                        </td>
                    </tr>

                    <TableRow
                        v-else
                        v-for="(row, i) in data"
                        :key="row.id ?? i"
                        :row="row"
                        :columns="columns"
                        :selectable="selectable"
                        :selected="isSelected(row)"
                        :has-actions="hasActions"
                        :index="i"
                        @toggle="toggleRow"
                    >
                        <template v-if="$slots.actions" #actions="{ row: r }">
                            <slot name="actions" :row="r" />
                        </template>
                    </TableRow>
                </tbody>
            </table>
        </div>
    </div>
</template>
