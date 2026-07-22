<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';

const props = defineProps({
    orders: Object,
});

const { formatPrice } = useHelpers();

const statusConfig = {
    pending:    { label: 'En attente',   color: 'bg-amber-50 text-amber-700 border-amber-200' },
    processing: { label: 'En préparation', color: 'bg-blue-50 text-blue-700 border-blue-200' },
    shipped:    { label: 'Expédiée',     color: 'bg-purple-50 text-purple-700 border-purple-200' },
    delivered:  { label: 'Livrée',       color: 'bg-green-50 text-green-700 border-green-200' },
    cancelled:  { label: 'Annulée',      color: 'bg-slate-100 text-slate-600 border-slate-200' },
    refunded:   { label: 'Remboursée',   color: 'bg-slate-100 text-slate-600 border-slate-200' },
};
</script>

<template>
    <FrontLayout title="Mes commandes">
        <Head title="Mes commandes" />

        <div class="min-h-screen bg-slate-50 py-8">
            <div class="container mx-auto px-4 max-w-4xl">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Mes commandes</h1>
                        <p class="text-sm text-slate-500 mt-0.5">{{ orders.total }} commande{{ orders.total > 1 ? 's' : '' }}</p>
                    </div>
                    <Link href="/mon-compte" class="text-sm text-slate-500 hover:text-slate-700 transition">
                        ← Mon compte
                    </Link>
                </div>

                <!-- Empty state -->
                <div v-if="orders.data.length === 0" class="bg-white rounded-xl border border-slate-200 py-16 text-center">
                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-slate-900 mb-1">Aucune commande</h2>
                    <p class="text-sm text-slate-500 mb-6">Vous n'avez pas encore passé de commande.</p>
                    <Link href="/boutique" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">
                        Découvrir la boutique
                    </Link>
                </div>

                <!-- Orders list -->
                <div v-else class="space-y-3">
                    <Link
                        v-for="order in orders.data"
                        :key="order.id"
                        :href="`/mon-compte/commandes/${order.id}`"
                        class="block bg-white rounded-xl border border-slate-200 hover:border-slate-300 hover:shadow-sm transition p-5"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <p class="text-sm font-semibold text-slate-900">#{{ order.order_number }}</p>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border"
                                        :class="statusConfig[order.status]?.color || 'bg-slate-100 text-slate-600 border-slate-200'"
                                    >
                                        {{ statusConfig[order.status]?.label || order.status }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400">{{ order.created_at }}</p>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ order.items_count }} article{{ order.items_count > 1 ? 's' : '' }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-base font-bold text-slate-900">{{ formatPrice(order.total) }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">Voir le détail →</p>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="orders.last_page > 1" class="mt-6 flex items-center justify-center gap-2">
                    <Link
                        v-if="orders.prev_page_url"
                        :href="orders.prev_page_url"
                        class="px-4 py-2 text-sm bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition"
                    >
                        ← Précédent
                    </Link>
                    <span class="text-sm text-slate-500 px-3">
                        {{ orders.current_page }} / {{ orders.last_page }}
                    </span>
                    <Link
                        v-if="orders.next_page_url"
                        :href="orders.next_page_url"
                        class="px-4 py-2 text-sm bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition"
                    >
                        Suivant →
                    </Link>
                </div>

            </div>
        </div>
    </FrontLayout>
</template>
