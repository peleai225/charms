<script setup>
import { MenuItem } from '@headlessui/vue'

const props = defineProps({
    href:   { type: String,  default: null },
    danger: { type: Boolean, default: false },
})
const emit = defineEmits(['click'])
</script>

<template>
    <MenuItem v-slot="{ active }">
        <component
            :is="href ? 'a' : 'button'"
            :href="href"
            :class="[
                'flex w-full items-center px-4 py-2 text-sm transition-colors',
                danger
                    ? (active ? 'bg-red-50 text-red-700' : 'text-red-600')
                    : (active ? 'bg-gray-50 text-gray-900' : 'text-gray-700'),
            ]"
            @click="emit('click', $event)"
        >
            <slot />
        </component>
    </MenuItem>
</template>
