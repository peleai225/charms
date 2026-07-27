<script setup>
import { ref, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'


const props = defineProps({
    orders: Object,
    stats:  Object,
    filters: Object,
})

const search        = ref(props.filters?.search ?? '')
const status        = ref(props.filters?.status ?? '')
const paymentStatus = ref(props.filters?.payment_status ?? '')
const dateRange     = ref(props.filters?.date_range ?? '')

let searchTimer = null
watch(search, v => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => applyFilters(), 300)
})
watch([status, paymentStatus, dateRange], () => applyFilters())

function applyFilters() {
    router.get(route('admin.orders.index'), {
        search:         search.value || undefined,
        status:         status.value || undefined,
        payment_status: paymentStatus.value || undefined,
        date_range:     dateRange.value || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value = ''
    status.value = ''
    paymentStatus.value = ''
    dateRange.value = ''
    applyFilters()
}

const hasFilters = computed(() =>
    search.value || status.value || paymentStatus.value || dateRange.value
)

// ── Drawer ──
const drawerOpen    = ref(false)
const drawerLoading = ref(false)
const drawerOrder   = ref(null)
const drawerSaving  = ref(false)

function openDrawer(orderId) {
    drawerOpen.value    = true
    drawerLoading.value = true
    drawerOrder.value   = null
    fetch(`/api/admin/order-detail/${orderId}`, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => { drawerOrder.value = d; drawerLoading.value = false })
    .catch(() => { drawerLoading.value = false })
}

function closeDrawer() {
    drawerOpen.value = false
}

async function changeStatus(newStatus) {
    if (!drawerOrder.value || drawerSaving.value) return
    drawerSaving.value = true
    try {
        const csrf = document.querySelector('meta[name=csrf-token]').content
        const res  = await fetch(`/admin/orders/${drawerOrder.value.id}/status`, {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ status: newStatus }),
        })
        const data = await res.json()
        if (data.success || data.status) {
            drawerOrder.value.status = newStatus
            // Mettre à jour le badge dans la table sans rechargement
            const row = document.querySelector(`[data-order-id="${drawerOrder.value.id}"]`)
            if (row) {
                const badge = row.querySelector('[data-status-badge]')
                if (badge) {
                    badge.className = `inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border ${statusClass(newStatus)}`
                    badge.textContent = statusLabel(newStatus)
                }
            }
        }
    } catch {}
    drawerSaving.value = false
}

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
    pending: 'bg-gray-50 text-gray-700 border-gray-200',
    paid: 'bg-green-50 text-green-700 border-green-200',
    partially_paid: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    failed: 'bg-red-50 text-red-700 border-red-200',
    refunded: 'bg-orange-50 text-orange-700 border-orange-200',
}
const PAYMENT_LABELS = {
    pending: 'En attente', paid: 'Payée', partially_paid: 'Partielle',
    failed: 'Échouée', refunded: 'Remboursée',
}

function statusLabel(s) { return STATUS_LABELS[s] ?? s }
function statusClass(s)  { return STATUS_CLASSES[s] ?? 'bg-gray-50 text-gray-700 border-gray-200' }
function paymentLabel(s) { return PAYMENT_LABELS[s] ?? s }
function paymentClass(s) { return PAYMENT_CLASSES[s] ?? 'bg-gray-50 text-gray-700 border-gray-200' }

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F'
}

const FLOW = ['pending', 'confirmed', 'processing', 'shipped', 'delivered']
function flowIndex(s) { return FLOW.indexOf(s) }
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Commandes</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">{{ orders.total }} commande(s) au total</p>
            </div>
        </div>

        <!-- KPI -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-5">
            <div class="grid grid-cols-2 lg:grid-cols-5 divide-x divide-y lg:divide-y-0 divide-gray-100">
                <div class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">En attente</p>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ stats.pending }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">En cours</p>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ stats.processing }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Expédiées</p>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ stats.shipped }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">Aujourd'hui</p>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ stats.today_count }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">CA Aujourd'hui</p>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ fmt(stats.today_total) }}</p>
                </div>
            </div>
        </div>

        <!-- Table card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

            <!-- Filtres -->
            <div class="p-4 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Recherche -->
                    <div class="flex-1 relative">
                        <input v-model="search" type="text"
                            placeholder="Rechercher par N°, email, nom..."
                            class="w-full h-9 px-3 pr-8 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    </div>

                    <!-- Statut -->
                    <select v-model="status" class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Tous les statuts</option>
                        <option value="pending">En attente</option>
                        <option value="confirmed">Confirmée</option>
                        <option value="processing">En préparation</option>
                        <option value="shipped">Expédiée</option>
                        <option value="delivery_in_progress">Livreur en route</option>
                        <option value="delivered">Livrée</option>
                        <option value="cancelled">Annulée</option>
                        <option value="refunded">Remboursée</option>
                    </select>

                    <!-- Paiement -->
                    <select v-model="paymentStatus" class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Tous paiements</option>
                        <option value="pending">En attente</option>
                        <option value="paid">Payée</option>
                        <option value="partially_paid">Partiellement payée</option>
                        <option value="failed">Échouée</option>
                        <option value="refunded">Remboursée</option>
                    </select>

                    <!-- Période -->
                    <select v-model="dateRange" class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Toutes dates</option>
                        <option value="today">Aujourd'hui</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                    </select>

                    <!-- Reset -->
                    <button v-if="hasFilters" @click="resetFilters"
                        class="h-9 px-3 flex items-center justify-center border border-gray-200 text-[13px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Tableau -->
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">N° Commande</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Client</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">Date</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">Montant</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Statut</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Paiement</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 whitespace-nowrap">Articles</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="orders.data.length === 0">
                            <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-[13px] font-medium text-gray-500">Aucune commande trouvée</p>
                            </td>
                        </tr>
                        <tr v-for="order in orders.data" :key="order.id"
                            :data-order-id="order.id"
                            class="hover:bg-blue-50/30 transition cursor-pointer group"
                            @click="openDrawer(order.id)">

                            <td class="px-4 py-3">
                                <span class="font-semibold text-gray-900 group-hover:text-blue-600 transition">
                                    {{ order.order_number }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ order.billing_first_name }} {{ order.billing_last_name }}</p>
                                <p class="text-gray-500 text-xs">{{ order.billing_email }}</p>
                            </td>

                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ order.created_at_fmt }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                                {{ fmt(order.total) }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span data-status-badge
                                    :class="statusClass(order.status)"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border">
                                    {{ statusLabel(order.status) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span :class="paymentClass(order.payment_status)"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border">
                                    {{ paymentLabel(order.payment_status) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center text-gray-600 tabular-nums">
                                {{ order.items_count }}
                            </td>

                            <td class="px-4 py-3 text-right" @click.stop>
                                <a :href="route('admin.orders.show', order.id)"
                                    class="inline-flex items-center gap-1 text-gray-400 hover:text-blue-600 transition opacity-0 group-hover:opacity-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="orders.last_page > 1" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between gap-4">
                <p class="text-[12px] text-gray-500">
                    Page {{ orders.current_page }} / {{ orders.last_page }}
                    &nbsp;·&nbsp; {{ orders.total }} résultats
                </p>
                <div class="flex items-center gap-1">
                    <a v-if="orders.prev_page_url" :href="orders.prev_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        ← Précédent
                    </a>
                    <template v-for="link in orders.links" :key="link.label">
                        <a v-if="link.url && !link.label.includes('Suivant') && !link.label.includes('Précédent')"
                            :href="link.url"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                            class="h-8 w-8 flex items-center justify-center text-[12px] font-medium border rounded-lg transition">
                            {{ link.label }}
                        </a>
                    </template>
                    <a v-if="orders.next_page_url" :href="orders.next_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                        Suivant →
                    </a>
                </div>
            </div>
        </div>

        <!-- Drawer -->
        <Teleport to="body">
            <div v-if="drawerOpen"
                class="fixed inset-0 z-[9990] flex"
                @keydown.escape.window="closeDrawer">

                <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" @click="closeDrawer"></div>

                <div class="absolute right-0 top-0 h-full w-full max-w-[520px] bg-white shadow-2xl flex flex-col
                            transition-transform duration-200"
                    @click.stop>

                    <!-- Header drawer -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div v-if="drawerLoading" class="h-4 w-32 bg-gray-100 rounded animate-pulse"></div>
                                <template v-else-if="drawerOrder">
                                    <p class="text-[15px] font-bold text-gray-900 leading-none">{{ drawerOrder.order_number }}</p>
                                    <p class="text-[12px] text-gray-400 mt-0.5">{{ drawerOrder.created_at }}</p>
                                </template>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a v-if="drawerOrder" :href="drawerOrder.show_url"
                                class="h-8 px-3 flex items-center gap-1.5 border border-gray-200 text-[12px] font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Détail
                            </a>
                            <button @click="closeDrawer" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Body drawer -->
                    <div class="flex-1 overflow-y-auto">
                        <!-- Skeleton -->
                        <div v-if="drawerLoading" class="p-5 space-y-4">
                            <div class="h-4 bg-gray-100 rounded w-1/2 animate-pulse"></div>
                            <div class="h-4 bg-gray-100 rounded w-3/4 animate-pulse"></div>
                            <div class="h-24 bg-gray-100 rounded animate-pulse mt-4"></div>
                            <div class="h-24 bg-gray-100 rounded animate-pulse"></div>
                        </div>

                        <div v-else-if="drawerOrder" class="divide-y divide-gray-100">

                            <!-- Statut -->
                            <div class="px-5 py-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest">Statut</p>
                                    <span :class="statusClass(drawerOrder.status)"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border">
                                        {{ statusLabel(drawerOrder.status) }}
                                    </span>
                                </div>
                                <!-- Barre de progression -->
                                <div class="flex items-center gap-0.5">
                                    <div v-for="(s, i) in FLOW" :key="s" class="flex-1 h-1.5 rounded-full transition-all duration-300"
                                        :class="flowIndex(drawerOrder.status) >= i
                                            ? (drawerOrder.status === 'delivered' ? 'bg-green-500' : 'bg-blue-600')
                                            : (drawerOrder.status === 'cancelled' ? 'bg-red-200' : 'bg-gray-100')">
                                    </div>
                                </div>
                                <!-- Boutons statut -->
                                <div class="grid grid-cols-3 gap-1.5 pt-1">
                                    <button v-for="s in ['pending','confirmed','processing','shipped','delivered','cancelled']" :key="s"
                                        @click="changeStatus(s)"
                                        :disabled="drawerOrder.status === s || drawerSaving"
                                        :class="drawerOrder.status === s
                                            ? statusClass(s) + ' ring-2 ring-offset-1 ring-blue-400 font-bold'
                                            : 'border-gray-200 text-gray-500 hover:border-blue-300 hover:text-blue-600 hover:bg-blue-50'"
                                        class="h-8 px-2 text-[11px] font-medium border rounded-lg transition flex items-center justify-center gap-1 disabled:opacity-60 disabled:cursor-not-allowed">
                                        <svg v-if="drawerSaving && drawerOrder.status !== s" class="w-3 h-3 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        {{ statusLabel(s) }}
                                    </button>
                                </div>
                            </div>

                            <!-- Client -->
                            <div class="px-5 py-4">
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Client</p>
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-sm font-bold flex-shrink-0">
                                        {{ (drawerOrder.customer_name || '??').substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0 space-y-1">
                                        <p class="text-[13px] font-semibold text-gray-900">{{ drawerOrder.customer_name }}</p>
                                        <p v-if="drawerOrder.billing_email" class="text-[12px] text-gray-500">{{ drawerOrder.billing_email }}</p>
                                        <p v-if="drawerOrder.billing_phone" class="text-[12px] text-gray-500">{{ drawerOrder.billing_phone }}</p>
                                        <p v-if="drawerOrder.billing_address" class="text-[12px] text-gray-500">{{ drawerOrder.billing_address }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Articles -->
                            <div class="px-5 py-4">
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">
                                    Articles ({{ drawerOrder.items.length }})
                                </p>
                                <div class="space-y-3">
                                    <div v-for="item in drawerOrder.items" :key="item.id" class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0 border border-gray-100">
                                            <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                            <div v-else class="w-full h-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-medium text-gray-900 truncate">{{ item.name }}</p>
                                            <p v-if="item.variant" class="text-[11px] text-gray-400">{{ item.variant }}</p>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <p class="text-[13px] font-semibold text-gray-900">{{ item.total }}</p>
                                            <p class="text-[11px] text-gray-400">x {{ item.quantity }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Totaux -->
                            <div class="px-5 py-4 space-y-2">
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Paiement</p>
                                <div class="flex items-center justify-between text-[13px]">
                                    <span class="text-gray-500">Sous-total</span>
                                    <span class="font-medium text-gray-900">{{ drawerOrder.subtotal_fmt }}</span>
                                </div>
                                <div v-if="drawerOrder.discount_fmt" class="flex items-center justify-between text-[13px]">
                                    <span class="text-green-600">Réduction</span>
                                    <span class="font-medium text-green-600">{{ drawerOrder.discount_fmt }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[13px]">
                                    <span class="text-gray-500">Livraison</span>
                                    <span class="font-medium text-gray-900">{{ drawerOrder.shipping_fmt }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-2.5 border-t border-gray-100 mt-1">
                                    <span class="text-[14px] font-bold text-gray-900">Total</span>
                                    <span class="text-[16px] font-bold text-blue-600">{{ drawerOrder.total_fmt }}</span>
                                </div>
                                <div class="flex items-center justify-between text-[12px] pt-1">
                                    <span class="text-gray-400">Paiement</span>
                                    <span :class="drawerOrder.payment_status === 'paid' ? 'text-green-600' : drawerOrder.payment_status === 'failed' ? 'text-red-600' : 'text-gray-500'"
                                        class="font-medium">{{ drawerOrder.payment_label }}</span>
                                </div>
                            </div>

                            <!-- Note client -->
                            <div v-if="drawerOrder.notes" class="px-5 py-4">
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-2">Note</p>
                                <p class="text-[12px] text-gray-600 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2.5">{{ drawerOrder.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer drawer -->
                    <div v-if="drawerOrder && !drawerLoading"
                        class="flex-shrink-0 border-t border-gray-100 px-5 py-3 bg-gray-50/80 flex items-center gap-2">
                        <a :href="drawerOrder.invoice_url" target="_blank"
                            class="flex-1 h-9 flex items-center justify-center gap-1.5 border border-gray-200 bg-white text-[12px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Facture
                        </a>
                        <a :href="drawerOrder.receipt_url + '?auto_print=1'" target="_blank"
                            class="flex-1 h-9 flex items-center justify-center gap-1.5 border border-gray-200 bg-white text-[12px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Ticket
                        </a>
                        <a :href="drawerOrder.show_url"
                            class="flex-1 h-9 flex items-center justify-center gap-1.5 bg-blue-600 text-white text-[12px] font-semibold rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Voir commande
                        </a>
                    </div>
                </div>
            </div>
        </Teleport>

    </div>
</template>
