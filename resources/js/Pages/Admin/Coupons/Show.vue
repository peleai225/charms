<script setup>
const props = defineProps({
    coupon: Object,
})

const STATUS_LABELS = {
    active:    'Actif',
    inactive:  'Inactif',
    expired:   'Expiré',
    scheduled: 'Programmé',
    exhausted: 'Épuisé',
}

const STATUS_CLASSES = {
    active:    'bg-green-50 text-green-700 border-green-200',
    inactive:  'bg-gray-100 text-gray-500 border-gray-200',
    expired:   'bg-red-50 text-red-700 border-red-200',
    scheduled: 'bg-blue-50 text-blue-700 border-blue-200',
    exhausted: 'bg-amber-50 text-amber-700 border-amber-200',
}

const TYPE_LABELS = {
    percentage:    'Pourcentage',
    fixed:         'Montant fixe',
    free_shipping: 'Livraison gratuite',
}

function statusLabel(s) { return STATUS_LABELS[s] ?? s }
function statusClass(s) {
    return (STATUS_CLASSES[s] ?? 'bg-gray-100 text-gray-500 border-gray-200') +
        ' inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border'
}
function typeLabel(t) { return TYPE_LABELS[t] ?? t }
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a :href="route('admin.coupons.index')"
                    class="p-2 hover:bg-gray-100 rounded-lg transition text-gray-500 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 font-mono">{{ coupon.code }}</h1>
                    <p class="text-[13px] text-gray-500 mt-0.5">{{ coupon.name }}</p>
                </div>
            </div>
            <a :href="route('admin.coupons.edit', coupon.id)"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
        </div>

        <div class="grid lg:grid-cols-3 gap-5">

            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Informations générales -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">Informations</h2>
                    <dl class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 mb-1">Code</dt>
                            <dd>
                                <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg text-[13px]">
                                    {{ coupon.code }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 mb-1">Nom</dt>
                            <dd class="text-[13px] font-medium text-gray-900">{{ coupon.name }}</dd>
                        </div>
                        <div v-if="coupon.description" class="sm:col-span-2">
                            <dt class="text-xs font-medium text-gray-500 mb-1">Description</dt>
                            <dd class="text-[13px] text-gray-700">{{ coupon.description }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Réduction -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">Réduction</h2>
                    <dl class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 mb-1">Type</dt>
                            <dd class="text-[13px] font-medium text-gray-900">{{ typeLabel(coupon.type) }}</dd>
                        </div>
                        <div v-if="coupon.type !== 'free_shipping'">
                            <dt class="text-xs font-medium text-gray-500 mb-1">Valeur</dt>
                            <dd class="text-[13px] font-semibold text-green-600">{{ coupon.type_label }}</dd>
                        </div>
                        <div v-else>
                            <dt class="text-xs font-medium text-gray-500 mb-1">Réduction</dt>
                            <dd class="text-[13px] font-semibold text-teal-600">Livraison offerte</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 mb-1">Montant min. de commande</dt>
                            <dd class="text-[13px] text-gray-900">{{ coupon.min_order_amount_fmt ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 mb-1">Réduction max.</dt>
                            <dd class="text-[13px] text-gray-900">{{ coupon.max_discount_amount_fmt ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Utilisations -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-900">Utilisations</h2>
                        <span class="text-[12px] text-gray-500">{{ coupon.usage_count }} au total</span>
                    </div>

                    <!-- Empty state -->
                    <div v-if="coupon.usages.length === 0" class="px-5 py-10 text-center">
                        <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-[13px] text-gray-400">Aucune utilisation pour le moment</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Client</th>
                                    <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commande</th>
                                    <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Remise</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="usage in coupon.usages" :key="usage.id"
                                    class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-3 text-gray-500">{{ usage.used_at }}</td>
                                    <td class="px-5 py-3 font-medium text-gray-900">
                                        {{ usage.customer_name ?? 'Invité' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <a v-if="usage.order_number"
                                            :href="route('admin.orders.show', usage.order_id)"
                                            class="font-mono text-blue-600 hover:underline">
                                            {{ usage.order_number }}
                                        </a>
                                        <span v-else class="text-gray-400">—</span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-medium text-gray-900">
                                        {{ usage.discount_amount_fmt ?? '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-5">

                <!-- Statut -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-900">Statut</h2>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] text-gray-500">Statut actuel</span>
                        <span :class="statusClass(coupon.status)">
                            {{ statusLabel(coupon.status) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] text-gray-500">Utilisations</span>
                        <span class="text-[13px] font-semibold text-gray-900">
                            {{ coupon.usage_count }}{{ coupon.usage_limit ? ' / ' + coupon.usage_limit : '' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] text-gray-500">Actif</span>
                        <span :class="coupon.is_active ? 'text-green-600' : 'text-gray-400'"
                            class="text-[13px] font-medium">
                            {{ coupon.is_active ? 'Oui' : 'Non' }}
                        </span>
                    </div>
                </div>

                <!-- Validité -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-3">
                    <h2 class="text-sm font-semibold text-gray-900">Validité</h2>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] text-gray-500">Début</span>
                        <span class="text-[13px] text-gray-900">{{ coupon.starts_at ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] text-gray-500">Expiration</span>
                        <span class="text-[13px] text-gray-900">{{ coupon.expires_at ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] text-gray-500">1ère commande seul.</span>
                        <span class="text-[13px] font-medium"
                            :class="coupon.first_order_only ? 'text-amber-600' : 'text-gray-400'">
                            {{ coupon.first_order_only ? 'Oui' : 'Non' }}
                        </span>
                    </div>
                </div>

                <!-- Limites -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-3">
                    <h2 class="text-sm font-semibold text-gray-900">Limites</h2>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] text-gray-500">Limite totale</span>
                        <span class="text-[13px] text-gray-900">{{ coupon.usage_limit ?? 'Illimitée' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] text-gray-500">Limite par client</span>
                        <span class="text-[13px] text-gray-900">{{ coupon.usage_limit_per_user ?? 'Illimitée' }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
