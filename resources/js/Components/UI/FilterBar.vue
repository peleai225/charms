<script setup>
import { computed } from 'vue'
import Badge from './Badge.vue'

const props = defineProps({
    modelValue: { type: Object, default: () => ({}) },
    defaults:   { type: Object, default: () => ({}) },
})
const emit = defineEmits(['update:modelValue', 'reset'])

const activeCount = computed(() => {
    return Object.keys(props.modelValue).filter(k => {
        const v = props.modelValue[k]
        const d = props.defaults[k]
        if (typeof v === 'object' && v !== null) return v.from || v.to
        return v !== '' && v !== null && v !== undefined && v !== d
    }).length
})
</script>

<template>
    <div class="flex flex-wrap items-end gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
        <slot />
        <div class="flex items-center gap-2 ml-auto">
            <Badge v-if="activeCount > 0" variant="info">{{ activeCount }} filtre{{ activeCount > 1 ? 's' : '' }}</Badge>
            <button
                v-if="activeCount > 0"
                class="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                @click="emit('reset')"
            >
                Réinitialiser
            </button>
        </div>
    </div>
</template>
