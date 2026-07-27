<script setup>
import { Search, X } from 'lucide-vue-next'

const props = defineProps({
    modelValue:  { type: String, default: '' },
    placeholder: { type: String, default: 'Rechercher...' },
})
const emit = defineEmits(['update:modelValue'])
</script>

<template>
    <div class="relative">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
        <input
            type="text"
            :value="modelValue"
            :placeholder="placeholder"
            class="w-full pl-9 pr-8 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @input="emit('update:modelValue', $event.target.value)"
        />
        <button
            v-if="modelValue"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            @click="emit('update:modelValue', '')"
            aria-label="Effacer"
        >
            <X class="w-4 h-4" />
        </button>
    </div>
</template>
