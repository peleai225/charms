<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    refunds: Object,
    filters: Object,
})

const status = ref(props.filters?.status ?? '')

watch(status, () => applyFilters())

function applyFilters() {
    router.get(route('admin.refunds.index'), {
        status: status.value || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    status.value = ''
    applyFilters()
}

const hasFilters = computed(() => !!status.value)

// ── Helpers ──
const STATUS_LABELS = {
    pending:   'En attente',
    approved:  'Approuvé',
    processed: 'Traité',
    rejected:  'Rejeté',
}
const STATUS_CLASSES = {
    pending:   'bg-amber-50 text-amber-700 border-amber-200',
    approved:  'bg-blue-50 text-blue-700 border-blue-200',
    processed: 'bg-green-50 text-green-700 border-green-200',
    rejected:  'bg-red-50 text-red-700 border-red-200',
}
function statusLabel(s) { return STATUS_LABELS[s] ?? s }
function statusClass(s) { return STATUS_CLASSES[s] ?? 'bg-gray-50 text-gray-700 border-gray-200' }

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F'
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold text-gray-900">Remboursements</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Suivez et gérez les demandes de remboursement</p>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex flex-wrap items-center gap-3">
                <select v-model="status"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="processed">Traité</option>
                    <option value="rejected">Rejeté</option>
                </select>

                <button v-if="hasFilters" @click="resetFilters"
                    class="h-9 px-3 inline-flex items-center gap-1.5 text-[13px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Effacer
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">N° Remboursement</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commande</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Montant</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Motif</th>
                            <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Traiteur</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <!-- Empty state -->
                        <tr v-if="refunds.data.length === 0">
                            <td colspan="7" class="px-5 py-16 text-center">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                                <p class="text-[13px] font-medium text-gray-500">Aucun remboursement</p>
                                <p class="text-[12px] text-gray-400 mt-1">Les remboursements apparaîtront ici</p>
                            </td>
                        </tr>

                        <tr v-for="refund in refunds.data" :key="refund.id"
                            class="hover:bg-gray-50/50 transition-colors">

                            <!-- N° Remboursement -->
                            <td class="px-5 py-4">
                                <span class="font-mono text-[13px] font-medium text-gray-900">{{ refund.refund_number }}</span>
                            </td>

                            <!-- Commande -->
                            <td class="px-5 py-4">
                                <a v-if="refund.order_id"
                                    :href="route('admin.orders.show', refund.order_id)"
                                    class="text-[13px] text-blue-600 font-medium hover:underline">
                                    {{ refund.order_number }}
                                </a>
                                <span v-else class="text-gray-400">—</span>
                            </td>

                            <!-- Montant -->
                            <td class="px-5 py-4 text-right">
                                <span class="text-[13px] font-bold text-gray-900">{{ fmt(refund.amount) }}</span>
                            </td>

                            <!-- Motif -->
                            <td class="px-5 py-4 text-[13px] text-gray-600">{{ refund.reason_label }}</td>

                            <!-- Statut -->
                            <td class="px-5 py-4 text-center">
                                <span :class="statusClass(refund.status)"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full border">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="{
                                            'bg-amber-500': refund.status === 'pending',
                                            'bg-blue-500':  refund.status === 'approved',
                                            'bg-green-500': refund.status === 'processed',
                                            'bg-red-500':   refund.status === 'rejected',
                                        }"></span>
                                    {{ statusLabel(refund.status) }}
                                </span>
                            </td>

                            <!-- Traiteur -->
                            <td class="px-5 py-4 text-[12px] text-gray-500">
                                {{ refund.processed_by_name ?? '—' }}
                            </td>

                            <!-- Date -->
                            <td class="px-5 py-4 text-[12px] text-gray-400 whitespace-nowrap">{{ refund.created_at_fmt }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="refunds.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-4">
                <p class="text-[12px] text-gray-500">
                    Page {{ refunds.current_page }} / {{ refunds.last_page }} &nbsp;·&nbsp; {{ refunds.total }} résultats
                </p>
                <div class="flex items-center gap-1">
                    <a v-if="refunds.prev_page_url" :href="refunds.prev_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        ← Précédent
                    </a>
                    <template v-for="link in refunds.links" :key="link.label">
                        <a v-if="link.url && !link.label.includes('Suivant') && !link.label.includes('Précédent')"
                            :href="link.url"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                            class="h-8 w-8 flex items-center justify-center text-[12px] font-medium border rounded-lg transition">
                            {{ link.label }}
                        </a>
                    </template>
                    <a v-if="refunds.next_page_url" :href="refunds.next_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        Suivant →
                    </a>
                </div>
            </div>
        </div>

    </div>
</template>
