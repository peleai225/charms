<script setup>
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';
import { computed } from 'vue';

const props = defineProps({
    orders: Object,
});

const { formatPrice } = useHelpers();

const statusConfig = {
    pending:    { label: 'En attente',    color: 'bg-amber-50 text-amber-700 border-amber-200', dot: 'bg-amber-400' },
    processing: { label: 'Préparation',   color: 'bg-blue-50 text-blue-700 border-blue-200',   dot: 'bg-blue-400' },
    shipped:    { label: 'Expédiée',      color: 'bg-purple-50 text-purple-700 border-purple-200', dot: 'bg-purple-400' },
    delivered:  { label: 'Livrée',        color: 'bg-green-50 text-green-700 border-green-200', dot: 'bg-green-400' },
    cancelled:  { label: 'Annulée',       color: 'bg-slate-100 text-slate-600 border-slate-200', dot: 'bg-slate-400' },
    refunded:   { label: 'Remboursée',    color: 'bg-slate-100 text-slate-600 border-slate-200', dot: 'bg-slate-400' },
};

const pageNumbers = computed(() => {
    const current = props.orders.current_page;
    const last    = props.orders.last_page;
    const pages   = [];
    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
            pages.push(i);
        } else if (pages[pages.length - 1] !== '...') {
            pages.push('...');
        }
    }
    return pages;
});

const goToPage = (p) => {
    if (p === '...') return;
    router.get('/mon-compte/commandes', { page: p }, { preserveScroll: true });
};
</script>

<template>
    <AccountLayout title="Mes commandes">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Mes commandes</h1>
                <p class="text-sm text-slate-500 mt-0.5">{{ orders.total }} commande{{ orders.total > 1 ? 's' : '' }}</p>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="orders.data.length === 0" class="bg-white rounded-xl border border-slate-200 py-16 text-center">
            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <h2 class="text-base font-semibold text-slate-900 mb-1">Aucune commande</h2>
            <p class="text-sm text-slate-500 mb-6">Vous n'avez pas encore passé de commande.</p>
            <Link href="/boutique" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition">
                Découvrir la boutique
            </Link>
        </div>

        <!-- Liste commandes -->
        <div v-else class="space-y-3">
            <Link
                v-for="order in orders.data"
                :key="order.id"
                :href="`/mon-compte/commandes/${order.id}`"
                class="block bg-white rounded-xl border border-slate-200 hover:border-slate-300 hover:shadow-sm transition p-4 sm:p-5"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <!-- Numéro + statut -->
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <p class="text-sm font-bold text-slate-900">#{{ order.order_number }}</p>
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium border"
                                :class="statusConfig[order.status]?.color || 'bg-slate-100 text-slate-600 border-slate-200'"
                            >
                                <span class="w-1.5 h-1.5 rounded-full" :class="statusConfig[order.status]?.dot || 'bg-slate-400'"></span>
                                {{ statusConfig[order.status]?.label || order.status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mb-2">{{ order.created_at }}</p>

                        <!-- Aperçu articles -->
                        <div v-if="order.items_preview?.length" class="flex -space-x-2">
                            <div
                                v-for="(item, i) in order.items_preview.slice(0, 3)"
                                :key="i"
                                class="w-9 h-9 rounded-lg border-2 border-white bg-slate-100 overflow-hidden shrink-0"
                            >
                                <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.name" class="w-full h-full object-cover" />
                            </div>
                            <div v-if="order.items_count > 3" class="w-9 h-9 rounded-lg border-2 border-white bg-slate-200 flex items-center justify-center shrink-0">
                                <span class="text-xs font-semibold text-slate-600">+{{ order.items_count - 3 }}</span>
                            </div>
                        </div>
                        <p v-else class="text-xs text-slate-500">{{ order.items_count }} article{{ order.items_count > 1 ? 's' : '' }}</p>
                    </div>

                    <!-- Prix + CTA -->
                    <div class="text-right shrink-0">
                        <p class="text-base font-bold text-slate-900">{{ formatPrice(order.total) }}</p>
                        <p class="text-xs text-slate-400 mt-0.5 flex items-center justify-end gap-0.5">
                            Détails
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </p>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="orders.last_page > 1" class="mt-6 flex items-center justify-center gap-1.5">
            <button :disabled="orders.current_page === 1" @click="goToPage(orders.current_page - 1)"
                class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg text-sm hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <template v-for="(p, i) in pageNumbers" :key="i">
                <span v-if="p === '...'" class="w-8 h-8 flex items-center justify-center text-slate-400 text-sm">…</span>
                <button v-else @click="goToPage(p)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition"
                    :class="p === orders.current_page ? 'bg-slate-900 text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50'">
                    {{ p }}
                </button>
            </template>
            <button :disabled="orders.current_page === orders.last_page" @click="goToPage(orders.current_page + 1)"
                class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg text-sm hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </AccountLayout>
</template>
