<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    customer: Object,
})

const STATUS_CLASSES = {
    active:   'bg-green-50 text-green-700 border-green-200',
    inactive: 'bg-gray-50 text-gray-700 border-gray-200',
    blocked:  'bg-red-50 text-red-700 border-red-200',
    vip:      'bg-purple-50 text-purple-700 border-purple-200',
}
const STATUS_LABELS = { active: 'Actif', inactive: 'Inactif', blocked: 'Bloqué', vip: 'VIP' }

const ORDER_STATUS_CLASSES = {
    pending:     'bg-yellow-50 text-yellow-700 border-yellow-200',
    confirmed:   'bg-blue-50 text-blue-700 border-blue-200',
    processing:  'bg-indigo-50 text-indigo-700 border-indigo-200',
    shipped:     'bg-purple-50 text-purple-700 border-purple-200',
    delivered:   'bg-green-50 text-green-700 border-green-200',
    cancelled:   'bg-red-50 text-red-700 border-red-200',
    refunded:    'bg-orange-50 text-orange-700 border-orange-200',
}
const ORDER_STATUS_LABELS = {
    pending: 'En attente', confirmed: 'Confirmée', processing: 'En préparation',
    shipped: 'Expédiée', delivered: 'Livrée', cancelled: 'Annulée', refunded: 'Remboursée',
}

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F'
}

// Changement de statut rapide
const quickStatus  = ref(props.customer.status)
const statusSaving = ref(false)
const statusDone   = ref(false)

async function updateStatus() {
    statusSaving.value = true
    router.patch(route('admin.customers.update', props.customer.id), {
        status: quickStatus.value,
        // champs requis par la validation — on renvoie les valeurs actuelles
        first_name: props.customer.first_name,
        last_name:  props.customer.last_name,
        email:      props.customer.email,
    }, {
        preserveState: true,
        onSuccess: () => { statusDone.value = true; setTimeout(() => { statusDone.value = false }, 2500) },
        onFinish: () => { statusSaving.value = false },
    })
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a :href="route('admin.customers.index')"
                    class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900">{{ customer.full_name }}</h1>
                        <span :class="STATUS_CLASSES[customer.status] ?? 'bg-gray-50 text-gray-700 border-gray-200'"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border">
                            {{ STATUS_LABELS[customer.status] ?? customer.status }}
                        </span>
                    </div>
                    <p class="text-[13px] text-gray-500 mt-0.5">Client depuis {{ customer.created_at_fmt }}</p>
                </div>
            </div>
            <a :href="route('admin.customers.edit', customer.id)"
                class="h-9 px-4 flex items-center gap-2 bg-[#2563EB] text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Modifier
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Colonne gauche -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Dernières commandes -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-900">Dernières commandes</h2>
                        <a :href="route('admin.orders.index')" class="text-[12px] text-blue-600 hover:text-blue-700 font-semibold">Voir tout →</a>
                    </div>
                    <div v-if="customer.orders.length" class="overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-2.5 text-left font-semibold text-gray-700">N° Commande</th>
                                    <th class="px-4 py-2.5 text-left font-semibold text-gray-700">Date</th>
                                    <th class="px-4 py-2.5 text-right font-semibold text-gray-700">Montant</th>
                                    <th class="px-4 py-2.5 text-center font-semibold text-gray-700">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="order in customer.orders" :key="order.id" class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2.5">
                                        <a :href="route('admin.orders.show', order.id)"
                                            class="font-semibold text-gray-900 hover:text-blue-600 transition">
                                            #{{ order.order_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">{{ order.created_at_fmt }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-gray-900 tabular-nums">{{ fmt(order.total) }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span :class="ORDER_STATUS_CLASSES[order.status] ?? 'bg-gray-50 text-gray-700 border-gray-200'"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border">
                                            {{ ORDER_STATUS_LABELS[order.status] ?? order.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="px-4 py-10 text-center">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-[13px] text-gray-500">Aucune commande</p>
                    </div>
                </div>

                <!-- Adresses -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-900">Adresses</h2>
                    </div>
                    <div v-if="customer.addresses.length" class="grid sm:grid-cols-2 gap-4 p-4">
                        <div v-for="addr in customer.addresses" :key="addr.id"
                            class="p-4 border border-gray-200 rounded-lg text-[13px]">
                            <span v-if="addr.is_default_shipping"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border bg-green-50 text-green-700 border-green-200 mb-2">
                                Par défaut
                            </span>
                            <p class="font-medium text-gray-900">{{ addr.first_name }} {{ addr.last_name }}</p>
                            <p v-if="addr.company" class="text-gray-500 mt-0.5">{{ addr.company }}</p>
                            <p class="text-gray-600 mt-1">{{ addr.address }}</p>
                            <p class="text-gray-600">{{ addr.postal_code }} {{ addr.city }}</p>
                            <p class="text-gray-600">{{ addr.country }}</p>
                            <p v-if="addr.phone" class="text-gray-500 mt-1.5">{{ addr.phone }}</p>
                        </div>
                    </div>
                    <div v-else class="px-4 py-10 text-center">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="text-[13px] text-gray-500">Aucune adresse enregistrée</p>
                    </div>
                </div>

            </div>

            <!-- Sidebar droite -->
            <div class="space-y-5">

                <!-- Informations -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-900">Informations</h2>
                    </div>
                    <div class="p-4 space-y-3 text-[13px]">
                        <div>
                            <p class="text-xs font-medium text-gray-700 mb-0.5">Email</p>
                            <p class="text-gray-900 break-all">{{ customer.email }}</p>
                        </div>
                        <div v-if="customer.phone">
                            <p class="text-xs font-medium text-gray-700 mb-0.5">Téléphone</p>
                            <p class="text-gray-900">{{ customer.phone }}</p>
                        </div>
                        <div v-if="customer.birth_date">
                            <p class="text-xs font-medium text-gray-700 mb-0.5">Date de naissance</p>
                            <p class="text-gray-900">{{ customer.birth_date }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-700 mb-0.5">Date d'inscription</p>
                            <p class="text-gray-900">{{ customer.created_at_fmt }}</p>
                        </div>
                        <div v-if="customer.notes">
                            <p class="text-xs font-medium text-gray-700 mb-0.5">Notes internes</p>
                            <p class="text-gray-600 whitespace-pre-wrap">{{ customer.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-900">Statistiques</h2>
                    </div>
                    <div class="divide-y divide-gray-100 text-[13px]">
                        <div class="px-4 py-3 flex items-center justify-between">
                            <span class="text-gray-600">Total commandes</span>
                            <span class="font-semibold text-gray-900 tabular-nums">{{ customer.orders_count }}</span>
                        </div>
                        <div class="px-4 py-3 flex items-center justify-between">
                            <span class="text-gray-600">CA total</span>
                            <span class="font-semibold text-gray-900 tabular-nums">{{ fmt(customer.total_spent) }}</span>
                        </div>
                        <div v-if="customer.loyalty_points > 0" class="px-4 py-3 flex items-center justify-between">
                            <span class="text-gray-600">Points fidélité</span>
                            <span class="font-semibold text-orange-600 tabular-nums">{{ customer.loyalty_points.toLocaleString('fr-FR') }} pts</span>
                        </div>
                    </div>
                </div>

                <!-- Changer statut -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-900">Changer le statut</h2>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="text-xs font-medium text-gray-700 block mb-1.5">Statut du compte</label>
                            <select v-model="quickStatus"
                                class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                                <option value="blocked">Bloqué</option>
                            </select>
                            <p class="mt-1.5 text-[11px] text-gray-400">Un client bloqué ne peut plus passer commande.</p>
                        </div>
                        <div v-if="statusDone" class="text-xs text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                            Statut mis à jour ✓
                        </div>
                        <button @click="updateStatus" :disabled="statusSaving"
                            class="w-full h-9 flex items-center justify-center bg-gray-900 text-white text-[13px] font-semibold rounded-lg hover:bg-gray-800 transition disabled:opacity-50">
                            <svg v-if="statusSaving" class="w-4 h-4 animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Mettre à jour
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>
