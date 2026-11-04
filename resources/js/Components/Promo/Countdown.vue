<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { remainingParts, compactText, clockOffset, pad } from './countdown.js'

// Compte à rebours jusqu'à endsAt. Disparaît une fois la date passée.
const props = defineProps({
    endsAt: { type: String, required: true },
    label: { type: String, default: 'Fin dans' },
    variant: { type: String, default: 'blocks' }, // blocks | inline
    tone: { type: String, default: 'light' }, // light (fond clair) | dark (fond sombre / image) | inherit (couleur du texte parent)
})
const emit = defineEmits(['ended'])

const page = usePage()
const offset = clockOffset(page.props.server_now, Date.now())
const now = ref(Date.now() + offset)
const endsAtMs = computed(() => Date.parse(props.endsAt))
const parts = computed(() => remainingParts(endsAtMs.value, now.value))
const running = computed(() => !Number.isNaN(endsAtMs.value) && parts.value.total > 0)

let timer = null
onMounted(() => {
    timer = setInterval(() => {
        now.value = Date.now() + offset
        if (!running.value) {
            clearInterval(timer)
            emit('ended')
        }
    }, 1000)
})
onUnmounted(() => clearInterval(timer))

const blocks = computed(() => [
    ...(parts.value.days > 0 ? [{ value: parts.value.days, unit: 'j' }] : []),
    { value: pad(parts.value.hours), unit: 'h' },
    { value: pad(parts.value.minutes), unit: 'min' },
    { value: pad(parts.value.seconds), unit: 's' },
])
</script>

<template>
    <div v-if="running" role="timer" :aria-label="`${label} ${compactText(parts)}`">
        <span v-if="variant === 'inline'" class="inline-flex items-center gap-1.5 font-semibold tabular-nums">
            <span class="font-normal opacity-80">{{ label }}</span>{{ compactText(parts) }}
        </span>
        <div v-else class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-semibold uppercase tracking-wide"
                :class="{ dark: 'text-white/80', light: 'text-slate-600', inherit: 'opacity-80' }[tone]">{{ label }}</span>
            <div class="flex items-center gap-1.5">
                <span v-for="b in blocks" :key="b.unit"
                    class="min-w-[44px] rounded-lg px-2 py-1.5 text-center tabular-nums"
                    :class="{ dark: 'bg-white/15 text-white', light: 'bg-slate-900 text-white', inherit: 'border border-current' }[tone]">
                    <span class="block text-base font-bold leading-none">{{ b.value }}</span>
                    <span class="block text-[10px] leading-none mt-1 opacity-70">{{ b.unit }}</span>
                </span>
            </div>
        </div>
    </div>
</template>
