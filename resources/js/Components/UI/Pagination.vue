<script setup>
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
    meta:  { type: Object, required: true },
    links: { type: Array,  default: () => [] },
})
const emit = defineEmits(['change'])

const pages = computed(() => {
    const total   = props.meta.last_page
    const current = props.meta.current_page
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

    const pagesSet = new Set([1, total, current, current - 1, current + 1].filter(p => p >= 1 && p <= total))
    const sorted = [...pagesSet].sort((a, b) => a - b)

    const withEllipsis = []
    for (let i = 0; i < sorted.length; i++) {
        withEllipsis.push(sorted[i])
        if (i < sorted.length - 1 && sorted[i + 1] - sorted[i] > 1) {
            withEllipsis.push('...')
        }
    }
    return withEllipsis
})
</script>

<template>
    <div class="flex items-center justify-between px-1 py-3">
        <p class="text-sm text-gray-500">
            Affichage <span class="font-medium">{{ meta.from }}</span>–<span class="font-medium">{{ meta.to }}</span>
            sur <span class="font-medium">{{ meta.total }}</span> résultats
        </p>

        <div class="flex items-center gap-1">
            <button
                class="p-1.5 rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                :disabled="meta.current_page === 1"
                @click="emit('change', meta.current_page - 1)"
                aria-label="Page précédente"
            >
                <ChevronLeft class="w-4 h-4" />
            </button>

            <template v-for="page in pages" :key="page">
                <span v-if="page === '...'" class="px-2 text-gray-400 text-sm">…</span>
                <button
                    v-else
                    :class="[
                        'min-w-[32px] h-8 px-2 rounded text-sm font-medium transition-colors',
                        page === meta.current_page
                            ? 'bg-blue-600 text-white'
                            : 'text-gray-700 hover:bg-gray-100',
                    ]"
                    @click="emit('change', page)"
                >
                    {{ page }}
                </button>
            </template>

            <button
                class="p-1.5 rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                :disabled="meta.current_page === meta.last_page"
                @click="emit('change', meta.current_page + 1)"
                aria-label="Page suivante"
            >
                <ChevronRight class="w-4 h-4" />
            </button>
        </div>
    </div>
</template>
