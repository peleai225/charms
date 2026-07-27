<script setup>
const props = defineProps({
    supplier:  Object,
    movements: Array,
    stats:     Object,
})

const MOVEMENT_TYPES = {
    purchase:       { label: 'Achat',              class: 'bg-green-50 text-green-700', entry: true },
    sale:           { label: 'Vente',              class: 'bg-red-50 text-red-700',     entry: false },
    return_in:      { label: 'Retour client',      class: 'bg-green-50 text-green-700', entry: true },
    return_out:     { label: 'Retour fournisseur', class: 'bg-red-50 text-red-700',     entry: false },
    adjustment_in:  { label: 'Ajustement +',       class: 'bg-blue-50 text-blue-700',   entry: true },
    adjustment_out: { label: 'Ajustement -',       class: 'bg-orange-50 text-orange-700', entry: false },
    transfer_in:    { label: 'Transfert entrant',  class: 'bg-green-50 text-green-700', entry: true },
    transfer_out:   { label: 'Transfert sortant',  class: 'bg-red-50 text-red-700',     entry: false },
    loss:           { label: 'Perte',              class: 'bg-red-50 text-red-700',     entry: false },
    inventory:      { label: 'Inventaire',         class: 'bg-gray-50 text-gray-700',   entry: true },
}

function typeInfo(type) {
    return MOVEMENT_TYPES[type] ?? { label: type, class: 'bg-gray-50 text-gray-700', entry: true }
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a :href="route('admin.suppliers.index')"
                    class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-gray-900">{{ supplier.name }}</h1>
                        <span v-if="supplier.is_active"
                            class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-50 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Actif
                        </span>
                        <span v-else
                            class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactif
                        </span>
                    </div>
                    <p v-if="supplier.code" class="text-[13px] text-gray-500 font-mono mt-0.5">{{ supplier.code }}</p>
                </div>
            </div>
            <a :href="route('admin.suppliers.edit', supplier.id)"
                class="h-9 px-4 flex items-center gap-2 bg-blue-600 text-white text-[13px] font-semibold rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Modifier
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Informations générales -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-900">Informations générales</h2>
                    </div>
                    <div class="p-4 grid sm:grid-cols-2 gap-4 text-[13px]">
                        <div v-if="supplier.contact_name">
                            <p class="text-xs font-medium text-gray-500 mb-0.5">Contact</p>
                            <p class="text-gray-900">{{ supplier.contact_name }}</p>
                        </div>
                        <div v-if="supplier.email">
                            <p class="text-xs font-medium text-gray-500 mb-0.5">Email</p>
                            <p class="text-gray-900 break-all">{{ supplier.email }}</p>
                        </div>
                        <div v-if="supplier.phone">
                            <p class="text-xs font-medium text-gray-500 mb-0.5">Téléphone</p>
                            <p class="text-gray-900">{{ supplier.phone }}</p>
                        </div>
                        <div v-if="supplier.payment_terms">
                            <p class="text-xs font-medium text-gray-500 mb-0.5">Délai de paiement</p>
                            <p class="text-gray-900">{{ supplier.payment_terms }} jours</p>
                        </div>
                        <div v-if="supplier.city || supplier.address" class="sm:col-span-2">
                            <p class="text-xs font-medium text-gray-500 mb-0.5">Adresse</p>
                            <p class="text-gray-900">
                                <span v-if="supplier.address">{{ supplier.address }}, </span>
                                <span v-if="supplier.postal_code">{{ supplier.postal_code }} </span>
                                <span v-if="supplier.city">{{ supplier.city }}</span>
                                <span v-if="supplier.country">, {{ supplier.country }}</span>
                            </p>
                        </div>
                        <div v-if="supplier.notes" class="sm:col-span-2">
                            <p class="text-xs font-medium text-gray-500 mb-0.5">Notes internes</p>
                            <p class="text-gray-600 whitespace-pre-wrap">{{ supplier.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Historique des mouvements -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-900">Historique des mouvements de stock</h2>
                    </div>
                    <div v-if="movements.length" class="overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Produit</th>
                                    <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                                    <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Qté</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="m in movements" :key="m.id" class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">{{ m.created_at_fmt }}</td>
                                    <td class="px-4 py-2.5 font-medium text-gray-900">{{ m.product_name ?? '—' }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span :class="typeInfo(m.type).class"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold">
                                            {{ typeInfo(m.type).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-semibold tabular-nums"
                                        :class="typeInfo(m.type).entry ? 'text-green-600' : 'text-red-600'">
                                        {{ typeInfo(m.type).entry ? '+' : '-' }}{{ m.quantity }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="px-4 py-12 text-center">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-[13px] text-gray-500">Aucun mouvement de stock enregistré</p>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-5">

                <!-- Statistiques -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-900">Statistiques</h2>
                    </div>
                    <div class="divide-y divide-gray-100 text-[13px]">
                        <div class="px-4 py-3 flex items-center justify-between">
                            <span class="text-gray-600">Total mouvements</span>
                            <span class="font-semibold text-gray-900 tabular-nums">{{ stats.total_movements }}</span>
                        </div>
                        <div class="px-4 py-3 flex items-center justify-between">
                            <span class="text-gray-600">Total entrées (qté)</span>
                            <span class="font-semibold text-green-600 tabular-nums">+{{ stats.total_in }}</span>
                        </div>
                        <div class="px-4 py-3 flex items-center justify-between">
                            <span class="text-gray-600">Créé le</span>
                            <span class="text-gray-900">{{ supplier.created_at_fmt }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>
