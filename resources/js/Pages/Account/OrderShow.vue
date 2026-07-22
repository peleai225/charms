<script setup>
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';

const props = defineProps({
    order: Object,
});

const { formatPrice } = useHelpers();

const statusConfig = {
    pending:    { label: 'En attente',    color: 'bg-amber-50 text-amber-700 border-amber-200', dot: 'bg-amber-400' },
    processing: { label: 'En préparation',color: 'bg-blue-50 text-blue-700 border-blue-200',   dot: 'bg-blue-400' },
    shipped:    { label: 'Expédiée',      color: 'bg-purple-50 text-purple-700 border-purple-200', dot: 'bg-purple-400' },
    delivered:  { label: 'Livrée',        color: 'bg-green-50 text-green-700 border-green-200', dot: 'bg-green-400' },
    cancelled:  { label: 'Annulée',       color: 'bg-slate-100 text-slate-600 border-slate-200', dot: 'bg-slate-400' },
};

const steps = ['pending', 'processing', 'shipped', 'delivered'];
const stepLabels = { pending: 'En attente', processing: 'Préparation', shipped: 'Expédiée', delivered: 'Livrée' };
const currentStepIndex = (status) => steps.indexOf(status);
</script>

<template>
    <AccountLayout :title="`Commande #${order.order_number}`">

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-5">
            <Link href="/mon-compte/commandes" class="hover:text-slate-700 transition">Commandes</Link>
            <span>/</span>
            <span class="text-slate-700 font-medium">#{{ order.order_number }}</span>
        </nav>

        <!-- Header commande -->
        <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Commande #{{ order.order_number }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Passée le {{ order.created_at }}</p>
            </div>
            <span
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border"
                :class="statusConfig[order.status]?.color"
            >
                <span class="w-1.5 h-1.5 rounded-full" :class="statusConfig[order.status]?.dot"></span>
                {{ statusConfig[order.status]?.label || order.status }}
            </span>
        </div>

        <!-- Tracker progression -->
        <div v-if="!['cancelled', 'refunded'].includes(order.status)" class="bg-white rounded-xl border border-slate-200 px-5 py-5 mb-4 overflow-x-auto">
            <div class="flex items-center min-w-[280px]">
                <template v-for="(step, i) in steps" :key="step">
                    <div class="flex flex-col items-center flex-1 relative">
                        <!-- Connecteur gauche -->
                        <div v-if="i > 0" class="absolute left-0 top-4 w-1/2 h-0.5 -translate-y-0.5"
                            :class="i <= currentStepIndex(order.status) ? 'bg-slate-900' : 'bg-slate-200'"></div>
                        <!-- Connecteur droit -->
                        <div v-if="i < steps.length - 1" class="absolute right-0 top-4 w-1/2 h-0.5 -translate-y-0.5"
                            :class="i < currentStepIndex(order.status) ? 'bg-slate-900' : 'bg-slate-200'"></div>

                        <!-- Rond -->
                        <div class="relative z-10 w-8 h-8 rounded-full border-2 flex items-center justify-center text-xs font-bold transition"
                            :class="i <= currentStepIndex(order.status)
                                ? 'bg-slate-900 border-slate-900 text-white'
                                : 'bg-white border-slate-200 text-slate-400'"
                        >
                            <svg v-if="i < currentStepIndex(order.status)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span v-else>{{ i + 1 }}</span>
                        </div>
                        <span class="text-xs mt-2 text-center" :class="i <= currentStepIndex(order.status) ? 'text-slate-900 font-medium' : 'text-slate-400'">
                            {{ stepLabels[step] }}
                        </span>
                    </div>
                </template>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <!-- ─── Articles + totaux ─────────────────────── -->
            <div class="md:col-span-2 space-y-4">
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100">
                        <p class="text-sm font-semibold text-slate-900">Articles commandés</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="item in order.items" :key="item.id" class="px-5 py-4 flex items-center gap-3">
                            <div class="w-14 h-14 bg-slate-100 rounded-xl overflow-hidden shrink-0">
                                <img v-if="item.product?.primary_image" :src="`/storage/${item.product.primary_image}`" :alt="item.name" class="w-full h-full object-cover" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 leading-tight">{{ item.name }}</p>
                                <p v-if="item.variant_name" class="text-xs text-slate-400 mt-0.5">{{ item.variant_name }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ item.quantity }} × {{ formatPrice(item.unit_price) }}</p>
                            </div>
                            <p class="text-sm font-bold text-slate-900 shrink-0">{{ formatPrice(item.unit_price * item.quantity) }}</p>
                        </div>
                    </div>

                    <!-- Totaux -->
                    <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Sous-total</span>
                            <span class="text-slate-700">{{ formatPrice(order.subtotal) }}</span>
                        </div>
                        <div v-if="order.discount_amount > 0" class="flex justify-between text-sm">
                            <span class="text-slate-500">Réduction</span>
                            <span class="text-green-600 font-medium">−{{ formatPrice(order.discount_amount) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Livraison</span>
                            <span class="text-slate-700">{{ order.shipping_cost > 0 ? formatPrice(order.shipping_cost) : 'Offerte' }}</span>
                        </div>
                        <div class="flex justify-between font-bold pt-2 border-t border-slate-200">
                            <span class="text-slate-900">Total</span>
                            <span class="text-slate-900">{{ formatPrice(order.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── Infos livraison + paiement ─────────── -->
            <div class="space-y-4">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">Livraison</p>
                    <p class="text-sm font-semibold text-slate-900">{{ order.shipping_first_name }} {{ order.shipping_last_name }}</p>
                    <p class="text-sm text-slate-500 mt-1 leading-relaxed">
                        {{ order.shipping_address }}<br />
                        {{ order.shipping_city }}{{ order.shipping_postal_code ? ' ' + order.shipping_postal_code : '' }}
                    </p>
                    <p v-if="order.shipping_phone" class="text-sm text-slate-500 mt-1">{{ order.shipping_phone }}</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">Paiement</p>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                :d="order.payment_method === 'cod'
                                    ? 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'
                                    : 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'"
                            />
                        </svg>
                        <p class="text-sm font-medium text-slate-900">
                            {{ order.payment_method === 'cod' ? 'À la livraison' : 'Paiement en ligne' }}
                        </p>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="order.notes" class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Notes</p>
                    <p class="text-sm text-slate-600 leading-relaxed">{{ order.notes }}</p>
                </div>
            </div>
        </div>
    </AccountLayout>
</template>
