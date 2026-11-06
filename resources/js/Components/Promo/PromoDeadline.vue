<script setup>
import { usePage } from '@inertiajs/vue3'
import Countdown from './Countdown.vue'
import { clockOffset, isEndingSoon, endDateText } from './countdown.js'

// Fin d'une promo : compte à rebours les 7 derniers jours, simple date avant.
// Un décompte de « 54 j » ne crée aucune urgence, il fait juste gadget.
defineProps({
    endsAt: { type: String, required: true },
    label: { type: String, default: 'Fin dans' },
    variant: { type: String, default: 'blocks' },
    tone: { type: String, default: 'light' },
})

const page = usePage()
const now = Date.now() + clockOffset(page.props.server_now, Date.now())
</script>

<template>
    <Countdown v-if="isEndingSoon(endsAt, now)" :ends-at="endsAt" :label="label" :variant="variant" :tone="tone" />
    <span v-else-if="Date.parse(endsAt) > now" class="text-sm">Jusqu'au {{ endDateText(endsAt) }}</span>
</template>
