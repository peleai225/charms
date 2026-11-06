<script setup>
import { useForm } from '@inertiajs/vue3'
import { useHelpers } from '@/Composables/useHelpers'
import { useToast } from '@/Composables/useToast'
import ConfirmModal from '@/Components/UI/ConfirmModal.vue'
import ToastContainer from '@/Components/UI/ToastContainer.vue'
import SettlementForm from './Partials/SettlementForm.vue'
import MovementHistory from './Partials/MovementHistory.vue'

const props = defineProps({
    courier: { type: Object, required: true },
    orders: { type: Array, default: () => [] },
    balance: { type: Number, default: 0 },
    movements: { type: Array, default: () => [] },
    whatsapp_url: { type: String, default: null },
    issues: { type: Array, default: () => [] },
})

const { formatPrice } = useHelpers()
const toast = useToast()

const payment = useForm({ amount: '', note: '', direction: 'from_courier' })
const DIRECTIONS = [
    { value: 'from_courier', label: "Le livreur m'a remis de l'argent" },
    { value: 'to_courier', label: "J'ai payé le livreur" },
    { value: 'correction_more', label: 'Correction : il doit plus' },
    { value: 'correction_less', label: 'Correction : il doit moins' },
]
function recordPayment() {
    payment.post(route('admin.couriers.payments.store', props.courier.id), {
        preserveScroll: true,
        onSuccess: (page) => { toast.success(page.props.flash?.success ?? 'Versement enregistré'); payment.reset() },
    })
}
</script>

<template>
    <div class="p-4 sm:p-6 space-y-5 max-w-3xl">
        <a :href="route('admin.couriers.index')" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900">← Livreurs</a>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ courier.name }}</h1>
                <p class="text-sm text-gray-500">
                    {{ courier.type === 'company' ? 'Société' : 'Indépendant' }} · {{ courier.phone }}
                    <span v-if="!courier.is_active" class="ml-1 text-amber-700">· inactif</span>
                </p>
            </div>
            <a v-if="whatsapp_url" :href="whatsapp_url" target="_blank" rel="noopener"
                class="h-11 px-4 inline-flex items-center justify-center text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">
                Envoyer la liste sur WhatsApp
            </a>
        </div>

        <!-- Solde -->
        <div class="rounded-xl border p-4" :class="balance > 0 ? 'border-amber-200 bg-amber-50' : balance < 0 ? 'border-blue-200 bg-blue-50' : 'border-green-200 bg-green-50'">
            <p class="text-xs font-medium text-gray-600">Solde</p>
            <p class="text-xl font-bold text-gray-900">
                <template v-if="balance > 0">Doit {{ formatPrice(balance) }} à la boutique</template>
                <template v-else-if="balance < 0">La boutique lui doit {{ formatPrice(-balance) }}</template>
                <template v-else>À jour</template>
            </p>
        </div>

        <!-- Point -->
        <section class="space-y-3">
            <h2 class="text-lg font-semibold text-gray-900">Faire le point · {{ orders.length }} colis en route</h2>
            <div v-if="!orders.length" class="rounded-xl border border-dashed border-gray-300 bg-white p-6 text-center">
                <p class="text-sm text-gray-600">Aucun colis chez ce livreur pour l'instant.</p>
                <a :href="route('admin.orders.index')" class="mt-2 inline-block text-sm font-semibold text-blue-700 hover:underline">Lui confier des commandes →</a>
            </div>
            <SettlementForm v-else :courier-id="courier.id" :orders="orders" :issues="issues" />
        </section>

        <!-- Versement -->
        <form class="rounded-xl border border-gray-200 bg-white p-4 space-y-3" @submit.prevent="recordPayment">
            <h2 class="text-sm font-semibold text-gray-900">Versement ou correction</h2>
            <p class="text-xs text-gray-500">Argent reçu plus tard (société qui reverse en fin de semaine), argent payé au livreur, ou correction d'une erreur de point.</p>
            <div class="flex flex-wrap items-end gap-3">
                <label class="w-full sm:w-64">
                    <span class="block text-xs font-medium text-gray-700 mb-1">Quoi ?</span>
                    <select v-model="payment.direction" class="w-full h-11 px-3 text-sm border border-gray-200 rounded-lg bg-white">
                        <option v-for="d in DIRECTIONS" :key="d.value" :value="d.value">{{ d.label }}</option>
                    </select>
                </label>
                <label class="w-44">
                    <span class="block text-xs font-medium text-gray-700 mb-1">Montant (F CFA) *</span>
                    <input v-model="payment.amount" type="number" min="1" step="1" required class="w-full h-11 px-3 text-sm border border-gray-200 rounded-lg">
                </label>
                <label class="flex-1 min-w-[160px]">
                    <span class="block text-xs font-medium text-gray-700 mb-1">Note{{ payment.direction.startsWith('correction') ? ' *' : '' }}</span>
                    <input v-model="payment.note" type="text" maxlength="500" placeholder="Ex : virement du vendredi, oubli au point du 03/11"
                        :required="payment.direction.startsWith('correction')" class="w-full h-11 px-3 text-sm border border-gray-200 rounded-lg">
                </label>
                <button type="submit" :disabled="payment.processing" class="h-11 px-4 text-sm font-semibold text-blue-700 border border-blue-300 rounded-lg hover:bg-blue-50 disabled:opacity-40">
                    Enregistrer
                </button>
            </div>
            <p v-if="payment.errors.amount" class="text-xs text-red-600">{{ payment.errors.amount }}</p>
            <p v-if="payment.errors.note" class="text-xs text-red-600">{{ payment.errors.note }}</p>
        </form>

        <MovementHistory :movements="movements" />

        <ConfirmModal />
        <ToastContainer />
    </div>
</template>
