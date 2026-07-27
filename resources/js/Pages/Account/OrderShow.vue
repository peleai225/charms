<script setup>
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useHelpers } from '@/Composables/useHelpers';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    order: Object,
});

const { formatPrice } = useHelpers();

const liveStatus      = ref(props.order.status);
const liveShippedAt   = ref(props.order.shipped_at ?? null);
const liveDeliveredAt = ref(props.order.delivered_at ?? null);
const statusToast     = ref(null); // message toast affiché lors d'un changement

const statusConfig = {
    pending:              { label: 'En attente',         color: 'bg-amber-50 text-amber-700 border-amber-200',   dot: 'bg-amber-400',   toast: 'Votre commande est en attente de traitement.' },
    confirmed:            { label: 'Confirmée',          color: 'bg-blue-50 text-blue-700 border-blue-200',      dot: 'bg-blue-400',    toast: 'Votre commande a été confirmée !' },
    processing:           { label: 'En préparation',     color: 'bg-blue-50 text-blue-700 border-blue-200',      dot: 'bg-blue-400',    toast: 'Votre commande est en cours de préparation.' },
    shipped:              { label: 'Expédiée',           color: 'bg-purple-50 text-purple-700 border-purple-200',dot: 'bg-purple-400',  toast: 'Votre commande a été expédiée !' },
    delivery_in_progress: { label: 'En livraison',       color: 'bg-indigo-50 text-indigo-700 border-indigo-200',dot: 'bg-indigo-400',  toast: 'Votre livreur est en route !' },
    delivered:            { label: 'Livrée',             color: 'bg-green-50 text-green-700 border-green-200',   dot: 'bg-green-400',   toast: 'Votre commande a été livrée. Merci !' },
    cancelled:            { label: 'Annulée',            color: 'bg-slate-100 text-slate-600 border-slate-200',  dot: 'bg-slate-400',   toast: 'Votre commande a été annulée.' },
};

const steps = ['pending', 'processing', 'shipped', 'delivered'];
const stepLabels = { pending: 'En attente', processing: 'Préparation', shipped: 'Expédiée', delivered: 'Livrée' };
const currentStepIndex = (status) => {
    const idx = steps.indexOf(status);
    // confirmed et processing sont au même niveau
    if (status === 'confirmed') return 1;
    if (status === 'delivery_in_progress') return 2;
    return idx;
};

// Polling toutes les 10 secondes — s'arrête quand statut final atteint
const FINAL_STATUSES = ['delivered', 'cancelled', 'refunded'];
let pollTimer = null;

async function pollStatus() {
    try {
        const res = await fetch(`/api/account/orders/${props.order.order_number}/status`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.status && data.status !== liveStatus.value) {
            liveStatus.value = data.status;
            if (data.shipped_at)   liveShippedAt.value   = data.shipped_at;
            if (data.delivered_at) liveDeliveredAt.value = data.delivered_at;
            // Toast in-app
            const msg = statusConfig[data.status]?.toast;
            if (msg) {
                statusToast.value = msg;
                setTimeout(() => { statusToast.value = null; }, 6000);
            }
            // Notification navigateur si permission accordée
            if (Notification.permission === 'granted') {
                new Notification('Chamse — Commande ' + props.order.order_number, {
                    body: msg,
                    icon: '/favicon.ico',
                });
            }
        }
        if (FINAL_STATUSES.includes(data.status)) stopPolling();
    } catch { /* silencieux */ }
}

function startPolling() {
    if (FINAL_STATUSES.includes(liveStatus.value)) return;
    pollTimer = setInterval(pollStatus, 2_000);
}

function stopPolling() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}

onMounted(() => {
    startPolling();
    // Demander permission notifs navigateur (silencieux si déjà décidé)
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
});
onUnmounted(stopPolling);
</script>

<template>
    <AccountLayout :title="`Commande #${order.order_number}`">

        <!-- Toast statut temps réel -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div v-if="statusToast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white text-sm font-medium px-5 py-3 rounded-full shadow-lg flex items-center gap-2.5 max-w-sm text-center">
                <span class="w-2 h-2 rounded-full shrink-0" :class="statusConfig[liveStatus]?.dot ?? 'bg-white'"></span>
                {{ statusToast }}
            </div>
        </Transition>

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
                :class="statusConfig[liveStatus]?.color"
            >
                <span class="w-1.5 h-1.5 rounded-full" :class="statusConfig[liveStatus]?.dot"></span>
                {{ statusConfig[liveStatus]?.label || liveStatus }}
            </span>
        </div>

        <!-- Tracker progression -->
        <div v-if="!['cancelled', 'refunded'].includes(liveStatus)" class="bg-white rounded-xl border border-slate-200 px-5 py-5 mb-4 overflow-x-auto">
            <div class="flex items-center min-w-[280px]">
                <template v-for="(step, i) in steps" :key="step">
                    <div class="flex flex-col items-center flex-1 relative">
                        <!-- Connecteur gauche -->
                        <div v-if="i > 0" class="absolute left-0 top-4 w-1/2 h-0.5 -translate-y-0.5"
                            :class="i <= currentStepIndex(liveStatus) ? 'bg-slate-900' : 'bg-slate-200'"></div>
                        <!-- Connecteur droit -->
                        <div v-if="i < steps.length - 1" class="absolute right-0 top-4 w-1/2 h-0.5 -translate-y-0.5"
                            :class="i < currentStepIndex(liveStatus) ? 'bg-slate-900' : 'bg-slate-200'"></div>

                        <!-- Rond -->
                        <div class="relative z-10 w-8 h-8 rounded-full border-2 flex items-center justify-center text-xs font-bold transition"
                            :class="i <= currentStepIndex(liveStatus)
                                ? 'bg-slate-900 border-slate-900 text-white'
                                : 'bg-white border-slate-200 text-slate-400'"
                        >
                            <svg v-if="i < currentStepIndex(liveStatus)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span v-else>{{ i + 1 }}</span>
                        </div>
                        <span class="text-xs mt-2 text-center" :class="i <= currentStepIndex(liveStatus) ? 'text-slate-900 font-medium' : 'text-slate-400'">
                            {{ stepLabels[step] }}
                        </span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Bloc "Livrée" -->
        <Transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100">
            <div v-if="liveStatus === 'delivered'" class="bg-green-50 border border-green-200 rounded-xl p-5 mb-4 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-green-900">Commande livrée !</p>
                    <p class="text-xs text-green-700 mt-0.5">
                        {{ liveDeliveredAt ? `Livrée le ${liveDeliveredAt}` : 'Votre colis a bien été remis.' }}
                        Merci pour votre confiance.
                    </p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <a href="/boutique" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-green-200 text-green-700 text-xs font-semibold rounded-lg hover:bg-green-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Racheter
                    </a>
                    <a href="#avis" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-lg hover:bg-green-700 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        Laisser un avis
                    </a>
                </div>
            </div>
        </Transition>

        <!-- Bloc "Annulée" -->
        <div v-if="liveStatus === 'cancelled'" class="bg-slate-50 border border-slate-200 rounded-xl p-5 mb-4 flex items-start gap-4">
            <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-slate-800">Commande annulée</p>
                <p class="text-xs text-slate-500 mt-0.5">Cette commande a été annulée. Si vous avez des questions, contactez-nous.</p>
            </div>
            <a href="/boutique" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50 transition shrink-0">
                Recréer une commande
            </a>
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
