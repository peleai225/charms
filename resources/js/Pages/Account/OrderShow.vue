<script setup>
import FrontLayout from '@/Layouts/FrontLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';

const props = defineProps({
    order: Object,
});

const { formatPrice } = useHelpers();

const statusConfig = {
    pending:    { label: 'En attente',     color: 'bg-amber-50 text-amber-700 border-amber-200', dot: 'bg-amber-400' },
    processing: { label: 'En préparation', color: 'bg-blue-50 text-blue-700 border-blue-200',   dot: 'bg-blue-400' },
    shipped:    { label: 'Expédiée',       color: 'bg-purple-50 text-purple-700 border-purple-200', dot: 'bg-purple-400' },
    delivered:  { label: 'Livrée',         color: 'bg-green-50 text-green-700 border-green-200', dot: 'bg-green-400' },
    cancelled:  { label: 'Annulée',        color: 'bg-slate-100 text-slate-600 border-slate-200', dot: 'bg-slate-400' },
};

const steps = ['pending', 'processing', 'shipped', 'delivered'];
const currentStepIndex = (status) => steps.indexOf(status);
</script>

<template>
    <FrontLayout :title="`Commande #${order.order_number}`">
        <Head :title="`Commande #${order.order_number}`" />

        <div class="min-h-screen bg-slate-50 py-8">
            <div class="container mx-auto px-4 max-w-3xl">

                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-slate-400 mb-6">
                    <Link href="/mon-compte" class="hover:text-slate-600 transition">Mon compte</Link>
                    <span>/</span>
                    <Link href="/mon-compte/commandes" class="hover:text-slate-600 transition">Commandes</Link>
                    <span>/</span>
                    <span class="text-slate-700 font-medium">#{{ order.order_number }}</span>
                </nav>

                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">Commande #{{ order.order_number }}</h1>
                        <p class="text-sm text-slate-500 mt-0.5">Passée le {{ order.created_at }}</p>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border"
                        :class="statusConfig[order.status]?.color"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="statusConfig[order.status]?.dot"></span>
                        {{ statusConfig[order.status]?.label || order.status }}
                    </span>
                </div>

                <!-- Progress tracker (si pas annulée) -->
                <div v-if="!['cancelled', 'refunded'].includes(order.status)" class="bg-white rounded-xl border border-slate-200 px-6 py-5 mb-4">
                    <div class="flex items-center justify-between relative">
                        <div class="absolute left-0 right-0 top-4 h-px bg-slate-200 z-0"></div>
                        <div
                            class="absolute left-0 top-4 h-px bg-blue-600 z-0 transition-all"
                            :style="{ width: `${(currentStepIndex(order.status) / (steps.length - 1)) * 100}%` }"
                        ></div>

                        <div v-for="(step, i) in steps" :key="step" class="relative z-10 flex flex-col items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-xs font-semibold transition"
                                :class="i <= currentStepIndex(order.status)
                                    ? 'bg-blue-600 border-blue-600 text-white'
                                    : 'bg-white border-slate-200 text-slate-400'"
                            >
                                <svg v-if="i < currentStepIndex(order.status)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span v-else>{{ i + 1 }}</span>
                            </div>
                            <span class="text-xs text-slate-500 whitespace-nowrap hidden sm:block">
                                {{ { pending: 'En attente', processing: 'Préparation', shipped: 'Expédiée', delivered: 'Livrée' }[step] }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <!-- Articles + totaux -->
                    <div class="md:col-span-2 space-y-4">

                        <!-- Articles -->
                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-100">
                                <p class="text-sm font-semibold text-slate-900">Articles</p>
                            </div>
                            <div class="divide-y divide-slate-100">
                                <div
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="px-5 py-4 flex items-center gap-3"
                                >
                                    <div class="w-14 h-14 bg-slate-100 rounded-lg overflow-hidden shrink-0">
                                        <img
                                            v-if="item.product?.primary_image"
                                            :src="`/storage/${item.product.primary_image}`"
                                            :alt="item.name"
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900 truncate">{{ item.name }}</p>
                                        <p v-if="item.variant_name" class="text-xs text-slate-400">{{ item.variant_name }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-semibold text-slate-900">{{ formatPrice(item.unit_price * item.quantity) }}</p>
                                        <p class="text-xs text-slate-400">{{ formatPrice(item.unit_price) }} × {{ item.quantity }}</p>
                                    </div>
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
                                    <span class="text-green-600">-{{ formatPrice(order.discount_amount) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Livraison</span>
                                    <span class="text-slate-700">{{ order.shipping_cost > 0 ? formatPrice(order.shipping_cost) : 'Offerte' }}</span>
                                </div>
                                <div class="flex justify-between text-sm font-semibold pt-2 border-t border-slate-200">
                                    <span class="text-slate-900">Total</span>
                                    <span class="text-slate-900">{{ formatPrice(order.total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Infos livraison + paiement -->
                    <div class="space-y-4">
                        <div class="bg-white rounded-xl border border-slate-200 px-5 py-4">
                            <p class="text-xs text-slate-500 uppercase tracking-wide font-medium mb-3">Livraison</p>
                            <p class="text-sm font-medium text-slate-900">{{ order.shipping_first_name }} {{ order.shipping_last_name }}</p>
                            <p class="text-sm text-slate-500 mt-1">{{ order.shipping_address }}</p>
                            <p class="text-sm text-slate-500">{{ order.shipping_city }}{{ order.shipping_postal_code ? ` ${order.shipping_postal_code}` : '' }}</p>
                            <p class="text-sm text-slate-500 mt-1">{{ order.shipping_phone }}</p>
                        </div>

                        <div class="bg-white rounded-xl border border-slate-200 px-5 py-4">
                            <p class="text-xs text-slate-500 uppercase tracking-wide font-medium mb-3">Paiement</p>
                            <p class="text-sm font-medium text-slate-900">
                                {{ order.payment_method === 'cod' ? 'À la livraison' : 'Paiement en ligne' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </FrontLayout>
</template>
