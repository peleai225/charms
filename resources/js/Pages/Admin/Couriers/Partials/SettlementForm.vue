<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useHelpers } from '@/Composables/useHelpers'
import { useConfirm } from '@/Composables/useConfirm'
import { useToast } from '@/Composables/useToast'

// Le point : pour chaque colis en route, livré ou pas, puis l'argent remis.
const props = defineProps({
    courierId: { type: Number, required: true },
    orders: { type: Array, required: true },
    issues: { type: Array, required: true },
})

const { formatPrice } = useHelpers()
const confirm = useConfirm()
const toast = useToast()

const ISSUE_LABELS = { absent: 'Client absent', injoignable: 'Client injoignable', refus: 'Refus du client', reportee: 'Livraison reportée', autre: 'Autre' }

const rows = ref([])
const amountReceived = ref('')
const receivedTouched = ref(false)
const note = ref('')
const sending = ref(false)

function resetRows() {
    rows.value = props.orders.map((o) => ({ ...o, outcome: null, issue: '', feeInput: o.fee ?? '' }))
    amountReceived.value = ''
    receivedTouched.value = false
    note.value = ''
}
resetRows()
watch(() => props.orders, resetRows)

const presented = computed(() => rows.value.filter((r) => r.outcome))
const collected = computed(() => presented.value.filter((r) => r.outcome === 'delivered').reduce((t, r) => t + r.to_collect, 0))
const fees = computed(() => presented.value.reduce((t, r) => t + (parseFloat(r.feeInput) || 0), 0))
const toRemit = computed(() => collected.value - fees.value)
const missingIssue = computed(() => presented.value.some((r) => r.outcome === 'failed' && !r.issue))

watch(toRemit, (v) => { if (!receivedTouched.value) amountReceived.value = v > 0 ? v : 0 })

function setOutcome(row, outcome) {
    row.outcome = row.outcome === outcome ? null : outcome
    if (row.outcome !== 'failed') row.issue = ''
}

async function withdraw(row) {
    const ok = await confirm({
        title: `Reprendre la commande ${row.order_number} ?`,
        message: "À utiliser si le colis n'est jamais parti : la commande revient « En préparation », sans frais pour le livreur.",
        confirmLabel: 'Reprendre',
    })
    if (ok) router.post(route('admin.orders.withdraw-courier', row.order_id), {}, { preserveScroll: true })
}

async function submit() {
    if (!presented.value.length || missingIssue.value) return
    const ok = await confirm({
        title: `Valider le point (${presented.value.length} colis) ?`,
        message: 'Les commandes livrées passent en « Livrée » et « Payée », les autres reviennent à relivrer. Un point validé ne se modifie plus.',
        confirmLabel: 'Valider le point',
    })
    if (!ok) return

    const outcomes = {}
    presented.value.forEach((r) => {
        outcomes[r.order_id] = { outcome: r.outcome, issue: r.outcome === 'failed' ? r.issue : null, fee: r.feeInput === '' ? null : r.feeInput }
    })

    sending.value = true
    router.post(route('admin.couriers.settle', props.courierId), { outcomes, amount_received: amountReceived.value || 0, note: note.value }, {
        preserveScroll: true,
        onSuccess: (page) => toast.success(page.props.flash?.success ?? 'Point enregistré'),
        onError: (errors) => toast.error(Object.values(errors)[0] ?? "Le point n'a pas pu être enregistré"),
        onFinish: () => { sending.value = false },
    })
}
</script>

<template>
    <div class="space-y-3">
        <div v-for="row in rows" :key="row.order_id" class="rounded-xl border bg-white p-4 space-y-3"
            :class="{ 'border-green-300': row.outcome === 'delivered', 'border-amber-300': row.outcome === 'failed', 'border-gray-200': !row.outcome }">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900">{{ row.order_number }} · {{ row.customer }}</p>
                    <p class="text-sm text-gray-600">
                        <a v-if="row.phone" :href="`tel:${row.phone}`" class="text-blue-700 underline">{{ row.phone }}</a>
                        <span v-if="row.city"> · {{ row.city }}</span>
                    </p>
                    <p v-if="row.address" class="text-xs text-gray-500">{{ row.address }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900">{{ row.to_collect ? formatPrice(row.to_collect) : 'Déjà payée' }}</p>
                    <p v-if="row.attempts" class="text-xs text-amber-700">{{ row.attempts }}ᵉ tentative ratée avant</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button type="button" class="h-11 rounded-lg border text-sm font-semibold"
                    :class="row.outcome === 'delivered' ? 'bg-green-600 border-green-600 text-white' : 'bg-white border-gray-200 text-gray-700'"
                    :aria-pressed="row.outcome === 'delivered'" @click="setOutcome(row, 'delivered')">Livrée</button>
                <button type="button" class="h-11 rounded-lg border text-sm font-semibold"
                    :class="row.outcome === 'failed' ? 'bg-amber-500 border-amber-500 text-white' : 'bg-white border-gray-200 text-gray-700'"
                    :aria-pressed="row.outcome === 'failed'" @click="setOutcome(row, 'failed')">Pas livrée</button>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <label v-if="row.outcome === 'failed'" class="flex-1 min-w-[180px]">
                    <span class="block text-xs font-medium text-gray-700 mb-1">Pourquoi ? *</span>
                    <select v-model="row.issue" class="w-full h-11 px-3 text-sm border rounded-lg bg-white"
                        :class="row.issue ? 'border-gray-200' : 'border-red-300'">
                        <option value="">Choisir…</option>
                        <option v-for="i in issues" :key="i" :value="i">{{ ISSUE_LABELS[i] ?? i }}</option>
                    </select>
                </label>
                <label class="w-36">
                    <span class="block text-xs font-medium text-gray-700 mb-1">
                        Frais livreur
                        <span v-if="row.fee === null" class="ml-1 rounded bg-amber-100 px-1.5 text-[10px] text-amber-800">zone inconnue</span>
                    </span>
                    <input v-model="row.feeInput" type="number" min="0" step="1" placeholder="0"
                        class="w-full h-11 px-3 text-sm border border-gray-200 rounded-lg">
                </label>
                <button type="button" class="h-11 px-3 text-sm text-gray-600 hover:underline" @click="withdraw(row)">Reprendre (pas parti)</button>
            </div>
        </div>

        <!-- Totaux -->
        <div class="sticky bottom-0 rounded-xl border border-gray-200 bg-white p-4 shadow-lg space-y-3">
            <div class="grid grid-cols-3 gap-2 text-center text-sm">
                <div><p class="text-xs text-gray-500">Encaissé</p><p class="font-semibold">{{ formatPrice(collected) }}</p></div>
                <div><p class="text-xs text-gray-500">Frais livreur</p><p class="font-semibold">{{ formatPrice(fees) }}</p></div>
                <div>
                    <p class="text-xs text-gray-500">{{ toRemit >= 0 ? 'À remettre' : 'On lui doit' }}</p>
                    <p class="font-bold" :class="toRemit >= 0 ? 'text-gray-900' : 'text-blue-700'">{{ formatPrice(Math.abs(toRemit)) }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <label class="w-44">
                    <span class="block text-xs font-medium text-gray-700 mb-1">Montant reçu maintenant</span>
                    <input v-model="amountReceived" type="number" min="0" step="1" class="w-full h-11 px-3 text-sm border border-gray-200 rounded-lg"
                        @input="receivedTouched = true">
                </label>
                <label class="flex-1 min-w-[160px]">
                    <span class="block text-xs font-medium text-gray-700 mb-1">Note</span>
                    <input v-model="note" type="text" maxlength="500" placeholder="Ex : manque 500 F, rendra demain"
                        class="w-full h-11 px-3 text-sm border border-gray-200 rounded-lg">
                </label>
                <button type="button" :disabled="!presented.length || missingIssue || sending"
                    class="h-11 px-5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-40" @click="submit">
                    {{ sending ? 'Enregistrement…' : `Valider le point (${presented.length})` }}
                </button>
            </div>
            <p v-if="missingIssue" class="text-xs text-red-600">Indiquez le motif de chaque commande non livrée.</p>
            <p v-else-if="!presented.length" class="text-xs text-gray-500">Cochez « Livrée » ou « Pas livrée » pour chaque colis ramené au point. Les autres restent chez le livreur.</p>
        </div>
    </div>
</template>
