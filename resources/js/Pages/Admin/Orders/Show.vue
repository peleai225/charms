<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    order:    Object,
    timeline: Array,
})

const STATUS_LABELS = {
    pending: 'En attente', confirmed: 'Confirmée', processing: 'En préparation',
    shipped: 'Expédiée', delivery_in_progress: 'Livreur en route',
    delivered: 'Livrée', cancelled: 'Annulée', refunded: 'Remboursée',
}
const STATUS_CLASSES = {
    pending: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    confirmed: 'bg-blue-50 text-blue-700 border-blue-200',
    processing: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    shipped: 'bg-purple-50 text-purple-700 border-purple-200',
    delivery_in_progress: 'bg-orange-50 text-orange-700 border-orange-200',
    delivered: 'bg-green-50 text-green-700 border-green-200',
    cancelled: 'bg-red-50 text-red-700 border-red-200',
    refunded: 'bg-gray-50 text-gray-700 border-gray-200',
}
const PAYMENT_CLASSES = {
    pending: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    paid: 'bg-green-50 text-green-700 border-green-200',
    failed: 'bg-red-50 text-red-700 border-red-200',
    refunded: 'bg-orange-50 text-orange-700 border-orange-200',
}

function statusLabel(s) { return STATUS_LABELS[s] ?? s }
function statusClass(s)  { return STATUS_CLASSES[s] ?? 'bg-gray-50 text-gray-700 border-gray-200' }
function paymentClass(s) { return PAYMENT_CLASSES[s] ?? 'bg-gray-50 text-gray-700 border-gray-200' }

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F'
}

// ── Changement de statut ──
const currentStatus   = ref(props.order.status)
const trackingNumber  = ref(props.order.tracking_number ?? '')
const shippingCarrier = ref(props.order.shipping_carrier ?? '')
const adminNote       = ref('')
const statusSaving    = ref(false)
const statusSuccess   = ref(false)

const showTracking = computed(() =>
    ['shipped', 'delivery_in_progress', 'delivered'].includes(currentStatus.value)
)

async function submitStatus() {
    statusSaving.value = true
    statusSuccess.value = false
    try {
        const csrf = document.querySelector('meta[name=csrf-token]').content
        const res = await fetch(`/admin/orders/${props.order.id}/status`, {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                status: currentStatus.value,
                tracking_number: trackingNumber.value || null,
                shipping_carrier: shippingCarrier.value || null,
                admin_notes: adminNote.value || null,
            }),
        })
        const data = await res.json()
        if (data.success) {
            statusSuccess.value = true
            if (adminNote.value) adminNote.value = ''
            setTimeout(() => { statusSuccess.value = false }, 3000)
        }
    } catch {}
    statusSaving.value = false
}

// ── Note rapide ──
const noteText    = ref('')
const noteSaving  = ref(false)
const adminNotes  = ref(props.order.admin_notes ?? '')

async function addNote() {
    if (!noteText.value.trim()) return
    noteSaving.value = true
    try {
        const csrf = document.querySelector('meta[name=csrf-token]').content
        const res = await fetch(`/admin/orders/${props.order.id}/note`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ note: noteText.value }),
        })
        const data = await res.json()
        if (data.success) {
            adminNotes.value = data.admin_notes
            noteText.value = ''
        }
    } catch {}
    noteSaving.value = false
}

// ── Renvoyer email ──
const resendLoading  = ref(false)
const resendDone     = ref(false)

async function resendEmail() {
    resendLoading.value = true
    try {
        const csrf = document.querySelector('meta[name=csrf-token]').content
        await fetch(`/admin/orders/${props.order.id}/resend`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        })
        resendDone.value = true
        setTimeout(() => { resendDone.value = false }, 3000)
    } catch {}
    resendLoading.value = false
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between gap-4 mb-5">
            <div class="flex items-center gap-3">
                <a :href="route('admin.orders.index')"
                    class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-600 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Commande {{ order.order_number }}</h1>
                    <p class="text-[13px] text-gray-500 mt-0.5">Passée le {{ order.created_at_fmt }}</p>
                </div>
                <span :class="statusClass(order.status)"
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border">
                    {{ statusLabel(order.status) }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="resendEmail" :disabled="resendLoading || resendDone"
                    class="h-9 px-4 flex items-center gap-2 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition disabled:opacity-50">
                    <svg v-if="resendLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    {{ resendDone ? 'Email envoyé ✓' : 'Renvoyer email' }}
                </button>
                <a :href="route('admin.orders.invoice.view', order.id)" target="_blank"
                    class="h-9 px-4 flex items-center gap-2 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Voir facture
                </a>
                <a :href="route('admin.orders.invoice', order.id)"
                    class="h-9 px-4 flex items-center gap-2 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Télécharger PDF
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Colonne gauche -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Articles -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-900">Articles ({{ order.items.length }})</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="item in order.items" :key="item.id" class="p-4 flex items-start gap-4">
                            <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ item.name }}</p>
                                <p v-if="item.variant_name" class="text-xs text-gray-500 mt-0.5">{{ item.variant_name }}</p>
                                <p class="text-xs text-gray-500 mt-1">Quantité : {{ item.quantity }}</p>
                                <p v-if="item.sku" class="text-xs text-gray-400 font-mono mt-0.5">{{ item.sku }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">{{ fmt(item.total) }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ fmt(item.unit_price) }} / unité</p>
                            </div>
                        </div>
                    </div>

                    <!-- Totaux -->
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 space-y-2">
                        <div class="flex justify-between text-[13px] text-gray-600">
                            <span>Sous-total</span>
                            <span class="font-medium">{{ fmt(order.subtotal) }}</span>
                        </div>
                        <div v-if="order.discount_amount > 0" class="flex justify-between text-[13px] text-green-600">
                            <span>Réduction <span v-if="order.coupon_code" class="font-mono text-xs">({{ order.coupon_code }})</span></span>
                            <span class="font-medium">-{{ fmt(order.discount_amount) }}</span>
                        </div>
                        <div v-if="order.shipping_amount > 0" class="flex justify-between text-[13px] text-gray-600">
                            <span>Livraison</span>
                            <span class="font-medium">{{ fmt(order.shipping_amount) }}</span>
                        </div>
                        <div v-if="order.tax_amount > 0" class="flex justify-between text-[13px] text-gray-600">
                            <span>TVA</span>
                            <span class="font-medium">{{ fmt(order.tax_amount) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200">
                            <span class="text-sm font-semibold text-gray-900">Total</span>
                            <span class="text-lg font-bold text-gray-900 tabular-nums">{{ fmt(order.total) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes internes -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Notes internes</h2>
                    <div v-if="adminNotes" class="text-[13px] text-gray-600 whitespace-pre-wrap bg-amber-50 border border-amber-100 rounded-lg px-3 py-2.5 mb-3">{{ adminNotes }}</div>
                    <div class="flex gap-2">
                        <input v-model="noteText" type="text" placeholder="Ajouter une note..."
                            class="flex-1 h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                            @keydown.enter="addNote">
                        <button @click="addNote" :disabled="noteSaving || !noteText.trim()"
                            class="h-9 px-4 bg-gray-900 text-white text-[13px] font-semibold rounded-lg hover:bg-gray-800 transition disabled:opacity-50">
                            <svg v-if="noteSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span v-else>Ajouter</span>
                        </button>
                    </div>
                </div>

                <!-- Note client -->
                <div v-if="order.customer_notes" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-2">Note du client</h2>
                    <p class="text-[13px] text-gray-600 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2.5">{{ order.customer_notes }}</p>
                </div>
            </div>

            <!-- Colonne droite -->
            <div class="space-y-5">

                <!-- Statut -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Statut de la commande</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1.5 block">Statut</label>
                            <select v-model="currentStatus"
                                class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="pending">En attente</option>
                                <option value="confirmed">Confirmée</option>
                                <option value="processing">En préparation</option>
                                <option value="shipped">Expédiée</option>
                                <option value="delivery_in_progress">Livreur en route</option>
                                <option value="delivered">Livrée</option>
                                <option value="cancelled">Annulée</option>
                            </select>
                        </div>

                        <template v-if="showTracking">
                            <div>
                                <label class="text-xs font-medium text-gray-700 mb-1.5 block">N° de suivi</label>
                                <input v-model="trackingNumber" type="text"
                                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                    placeholder="Ex: 1Z999AA10123456784">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-700 mb-1.5 block">Transporteur</label>
                                <input v-model="shippingCarrier" type="text"
                                    class="w-full px-3 py-2 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                    placeholder="Ex: DHL, FedEx">
                            </div>
                        </template>

                        <div v-if="statusSuccess"
                            class="text-xs text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                            Statut mis à jour avec succès ✓
                        </div>

                        <button @click="submitStatus" :disabled="statusSaving"
                            class="w-full h-9 bg-gray-900 text-white text-[13px] font-semibold rounded-lg hover:bg-gray-800 transition disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg v-if="statusSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ statusSaving ? 'Mise à jour...' : 'Mettre à jour' }}
                        </button>
                    </div>

                    <!-- Dates -->
                    <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Créée</span>
                            <span class="text-gray-900 font-medium">{{ order.created_at_fmt }}</span>
                        </div>
                        <div v-if="order.shipped_at_fmt" class="flex justify-between text-xs">
                            <span class="text-gray-500">Expédiée</span>
                            <span class="text-gray-900 font-medium">{{ order.shipped_at_fmt }}</span>
                        </div>
                        <div v-if="order.delivered_at_fmt" class="flex justify-between text-xs">
                            <span class="text-gray-500">Livrée</span>
                            <span class="text-gray-900 font-medium">{{ order.delivered_at_fmt }}</span>
                        </div>
                    </div>
                </div>

                <!-- Paiement -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Paiement</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between text-[13px]">
                            <span class="text-gray-500">Statut</span>
                            <span :class="paymentClass(order.payment_status)"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border">
                                {{ order.payment_status_label }}
                            </span>
                        </div>
                        <div class="flex justify-between text-[13px]">
                            <span class="text-gray-500">Méthode</span>
                            <span class="text-gray-900 font-medium">{{ order.payment_method_label }}</span>
                        </div>
                        <div v-if="order.paid_at_fmt" class="flex justify-between text-[13px]">
                            <span class="text-gray-500">Payée le</span>
                            <span class="text-gray-900 font-medium">{{ order.paid_at_fmt }}</span>
                        </div>
                    </div>
                </div>

                <!-- Client -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Client</h2>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-900">{{ order.billing_first_name }} {{ order.billing_last_name }}</p>
                        <a v-if="order.billing_email" :href="`mailto:${order.billing_email}`"
                            class="text-xs text-blue-600 hover:text-blue-700 transition block">{{ order.billing_email }}</a>
                        <p v-if="order.billing_phone" class="text-xs text-gray-600">{{ order.billing_phone }}</p>
                        <a v-if="order.customer_id" :href="route('admin.customers.show', order.customer_id)"
                            class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 transition mt-1">
                            Voir le profil client →
                        </a>
                    </div>
                </div>

                <!-- Adresse facturation -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Adresse de facturation</h2>
                    <div class="text-[13px] text-gray-600 space-y-0.5">
                        <p>{{ order.billing_address }}</p>
                        <p v-if="order.billing_address_2">{{ order.billing_address_2 }}</p>
                        <p>{{ order.billing_postal_code }} {{ order.billing_city }}</p>
                        <p>{{ order.billing_country }}</p>
                    </div>
                </div>

                <!-- Adresse livraison -->
                <div v-if="order.shipping_address" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Adresse de livraison</h2>
                    <div class="text-[13px] text-gray-600 space-y-0.5">
                        <p class="font-medium text-gray-900">{{ order.shipping_first_name }} {{ order.shipping_last_name }}</p>
                        <p v-if="order.shipping_phone">{{ order.shipping_phone }}</p>
                        <p>{{ order.shipping_address }}</p>
                        <p v-if="order.shipping_address_2">{{ order.shipping_address_2 }}</p>
                        <p>{{ order.shipping_postal_code }} {{ order.shipping_city }}</p>
                        <p>{{ order.shipping_country }}</p>
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>
