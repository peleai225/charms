<script setup>
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';

const props = defineProps({
    stats: Object,
});

const { formatPrice } = useHelpers();

const statCards = [
    { key: 'orders_count',     label: 'Commandes',  icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', color: 'text-primary-600 bg-primary-50' },
    { key: 'orders_delivered', label: 'Livrées',    icon: 'M5 13l4 4L19 7',                              color: 'text-green-600 bg-green-50' },
    { key: 'orders_pending',   label: 'En cours',   icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', color: 'text-amber-600 bg-amber-50' },
    { key: 'loyalty_points',   label: 'Pts fidélité',icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', color: 'text-purple-600 bg-purple-50' },
];

const quickLinks = [
    { href: '/mon-compte/commandes', label: 'Mes commandes',  desc: 'Suivez vos commandes en cours',   icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' },
    { href: '/mon-compte/adresses',  label: 'Mes adresses',   desc: 'Gérez vos adresses de livraison', icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z' },
    { href: '/mon-compte/fidelite',  label: 'Programme fidélité', desc: `${props.stats?.loyalty_points || 0} pts disponibles`, icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z' },
    { href: '/mon-compte/favoris',   label: 'Mes favoris',    desc: 'Vos produits coup de cœur',       icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z' },
];
</script>

<template>
    <AccountLayout title="Mon compte">
        <!-- Titre -->
        <div class="mb-5">
            <h1 class="text-xl font-bold text-slate-900">Tableau de bord</h1>
            <p class="text-sm text-slate-500 mt-0.5">Bienvenue dans votre espace personnel</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <div
                v-for="card in statCards"
                :key="card.key"
                class="bg-white rounded-xl border border-slate-200 p-4"
            >
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" :class="card.color">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-slate-900 tabular-nums">{{ stats?.[card.key] ?? 0 }}</p>
                        <p class="text-xs text-slate-500">{{ card.label }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières commandes -->
        <div v-if="stats?.recent_orders?.length" class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-5">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-900">Dernières commandes</p>
                <Link href="/mon-compte/commandes" class="text-xs text-slate-500 hover:text-slate-900 transition">Tout voir →</Link>
            </div>
            <div class="divide-y divide-slate-100">
                <Link
                    v-for="order in stats.recent_orders"
                    :key="order.id"
                    :href="`/mon-compte/commandes/${order.id}`"
                    class="flex items-center justify-between px-5 py-3.5 hover:bg-slate-50 transition"
                >
                    <div class="flex items-center gap-3">
                        <p class="text-sm font-medium text-slate-900">#{{ order.order_number }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium border"
                            :class="{
                                'bg-amber-50 text-amber-700 border-amber-200': order.status === 'pending',
                                'bg-blue-50 text-blue-700 border-blue-200': order.status === 'processing',
                                'bg-purple-50 text-purple-700 border-purple-200': order.status === 'shipped',
                                'bg-green-50 text-green-700 border-green-200': order.status === 'delivered',
                                'bg-slate-100 text-slate-600 border-slate-200': order.status === 'cancelled',
                            }">
                            {{ { pending: 'En attente', processing: 'Préparation', shipped: 'Expédiée', delivered: 'Livrée', cancelled: 'Annulée' }[order.status] || order.status }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-slate-900">{{ formatPrice(order.total) }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Accès rapide -->
        <div class="grid sm:grid-cols-2 gap-3">
            <Link
                v-for="link in quickLinks"
                :key="link.href"
                :href="link.href"
                class="bg-white rounded-xl border border-slate-200 hover:border-slate-300 hover:shadow-sm transition p-4 flex items-center gap-4"
            >
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="link.icon" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900">{{ link.label }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ link.desc }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </Link>
        </div>
    </AccountLayout>
</template>
