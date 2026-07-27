<script setup>
import { computed } from 'vue'
import * as LucideIcons from 'lucide-vue-next'

const props = defineProps({
    icon:        { type: String,  default: 'inbox' },
    title:       { type: String,  required: true },
    description: { type: String,  default: '' },
})

const IconComponent = computed(() => {
    const name = props.icon.charAt(0).toUpperCase() + props.icon.slice(1).replace(/-([a-z])/g, (_, c) => c.toUpperCase())
    return LucideIcons[name] || LucideIcons['Inbox']
})
</script>

<template>
    <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
            <component :is="IconComponent" class="w-6 h-6 text-gray-400" />
        </div>
        <h3 class="text-base font-semibold text-gray-900 mb-1">{{ title }}</h3>
        <p v-if="description" class="text-sm text-gray-500 mb-6 max-w-sm">{{ description }}</p>
        <div v-if="$slots.default" class="mt-2">
            <slot />
        </div>
    </div>
</template>
