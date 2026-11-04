<script setup>
import { useHelpers } from '@/Composables/useHelpers'

// Historique des points et versements d'un livreur (figés)
defineProps({
    movements: { type: Array, default: () => [] },
})

const { formatPrice } = useHelpers()
const ISSUE_LABELS = { absent: 'absent', injoignable: 'injoignable', refus: 'refus', reportee: 'reportée', autre: 'autre' }
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white">
        <h2 class="px-4 py-3 border-b border-gray-100 text-sm font-semibold text-gray-900">Historique</h2>
        <p v-if="!movements.length" class="px-4 py-6 text-sm text-gray-500 text-center">Aucun point ni versement pour l'instant.</p>
        <ul v-else class="divide-y divide-gray-100">
            <li v-for="m in movements" :key="m.id" class="px-4 py-3 space-y-1.5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-medium text-gray-900">{{ m.type === 'payment' ? 'Versement' : 'Point' }} · <span class="font-normal text-gray-500">{{ m.date }}</span></p>
                    <p class="text-sm font-semibold" :class="m.balance_change > 0 ? 'text-amber-700' : m.balance_change < 0 ? 'text-blue-700' : 'text-gray-600'">
                        Solde {{ m.balance_change > 0 ? '+' : '' }}{{ formatPrice(m.balance_change) }}
                    </p>
                </div>
                <p v-if="m.type === 'point'" class="text-xs text-gray-600">
                    Encaissé {{ formatPrice(m.amount_collected) }} · frais {{ formatPrice(m.fees) }} · reçu {{ formatPrice(m.amount_received) }}
                </p>
                <p v-else class="text-xs text-gray-600">Reçu {{ formatPrice(m.amount_received) }}</p>
                <div v-if="m.orders.length" class="flex flex-wrap gap-1">
                    <span v-for="o in m.orders" :key="o.order_number" class="rounded-full px-2 py-0.5 text-[11px]"
                        :class="o.outcome === 'delivered' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'">
                        {{ o.order_number }} {{ o.outcome === 'delivered' ? '✓' : `✗ ${ISSUE_LABELS[o.issue] ?? ''}` }}
                    </span>
                </div>
                <p v-if="m.note" class="text-xs text-gray-500">« {{ m.note }} »</p>
            </li>
        </ul>
    </div>
</template>
