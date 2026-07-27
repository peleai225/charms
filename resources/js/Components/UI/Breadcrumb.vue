<script setup>
import { Link } from '@inertiajs/vue3'
import { ChevronRight } from 'lucide-vue-next'

defineProps({
    items: { type: Array, required: true },
})
</script>

<template>
    <nav aria-label="Fil d'Ariane">
        <ol class="flex items-center gap-1 text-sm">
            <li v-for="(item, i) in items" :key="i" class="flex items-center gap-1">
                <ChevronRight v-if="i > 0" class="w-3.5 h-3.5 text-gray-300 shrink-0" />
                <Link
                    v-if="item.href && i < items.length - 1"
                    :href="item.href"
                    class="text-gray-500 hover:text-gray-700 transition-colors"
                >
                    {{ item.label }}
                </Link>
                <span v-else :class="i === items.length - 1 ? 'text-gray-900 font-medium' : 'text-gray-500'">
                    {{ item.label }}
                </span>
            </li>
        </ol>
    </nav>
</template>
