<script setup>
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    affiliate: Object,
    commissions: Array,
    commission_rate: Number,
    min_withdrawal: Number,
});

const applyForm = useForm({
    payment_method: '',
});

const paymentForm = useForm({
    payment_method: props.affiliate?.payment_method || '',
});

const copied = ref(false);

const copyLink = () => {
    if (!props.affiliate?.share_url) return;
    navigator.clipboard.writeText(props.affiliate.share_url);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
};

const submitApply = () => {
    applyForm.post('/mon-compte/affiliation/apply');
};

const submitPayment = () => {
    paymentForm.put('/mon-compte/affiliation/payment');
};

const requestWithdrawal = () => {
    if (!confirm('Confirmer la demande de retrait ?')) return;
    router.post('/mon-compte/affiliation/withdraw');
};

const confirmedBalance = computed(() => {
    if (!props.commissions) return 0;
    return props.commissions
        .filter(c => c.status === 'confirmed')
        .reduce((sum, c) => sum + parseFloat(c.commission_amount), 0);
});

const canWithdraw = computed(() => confirmedBalance.value >= props.min_withdrawal);

const fmt = (v) => new Intl.NumberFormat('fr-FR', { style: 'decimal', maximumFractionDigits: 0 }).format(v) + ' F CFA';

const statusLabel = (s) => ({ pending: 'En attente', confirmed: 'Confirmée', paid: 'Payée' }[s] || s);
const statusColor = (s) => ({
    pending: 'bg-amber-50 text-amber-700',
    confirmed: 'bg-blue-50 text-blue-700',
    paid: 'bg-green-50 text-green-700',
}[s] || 'bg-slate-100 text-slate-600');
</script>

<template>
    <AccountLayout title="Affiliation">
        <!-- État 1 : Non affilié -->
        <div v-if="!affiliate" class="max-w-lg mx-auto">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8">
                <div class="text-center mb-6">
                    <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900">Programme d'affiliation</h2>
                    <p class="text-sm text-slate-500 mt-1">Gagnez {{ commission_rate }}% de commission sur chaque vente générée par votre lien.</p>
                </div>

                <div class="space-y-3 mb-6 text-sm text-slate-600">
                    <div class="flex gap-3">
                        <span class="w-6 h-6 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-xs font-bold shrink-0">1</span>
                        <span>Inscrivez-vous au programme</span>
                    </div>
                    <div class="flex gap-3">
                        <span class="w-6 h-6 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-xs font-bold shrink-0">2</span>
                        <span>Partagez votre lien sur WhatsApp, Facebook…</span>
                    </div>
                    <div class="flex gap-3">
                        <span class="w-6 h-6 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-xs font-bold shrink-0">3</span>
                        <span>Gagnez {{ commission_rate }}% sur chaque commande</span>
                    </div>
                </div>

                <form @submit.prevent="submitApply" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Moyen de paiement pour recevoir vos gains</label>
                        <input v-model="applyForm.payment_method" type="text" required
                               placeholder="Ex : Wave 0707070707, Orange Money 0505…"
                               class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p v-if="applyForm.errors.payment_method" class="text-red-500 text-xs mt-1">{{ applyForm.errors.payment_method }}</p>
                    </div>
                    <button type="submit" :disabled="applyForm.processing"
                            class="w-full py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50 transition">
                        Devenir affilié
                    </button>
                </form>
            </div>
        </div>

        <!-- État 2 : En attente -->
        <div v-else-if="affiliate.status === 'pending'" class="max-w-lg mx-auto">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 text-center">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-slate-900">Demande en cours de traitement</h2>
                <p class="text-sm text-slate-500 mt-1 mb-6">L'équipe examine votre candidature. Vous serez notifié par email.</p>

                <form @submit.prevent="submitPayment" class="text-left space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Moyen de paiement</label>
                        <input v-model="paymentForm.payment_method" type="text" required
                               class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <button type="submit" :disabled="paymentForm.processing"
                            class="py-2 px-4 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800 disabled:opacity-50 transition">
                        Mettre à jour
                    </button>
                </form>
            </div>
        </div>

        <!-- État 3 : Affilié actif -->
        <div v-else-if="affiliate.status === 'active'" class="space-y-5">
            <!-- Lien de partage -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Votre lien d'affiliation</h3>
                <div class="flex gap-2">
                    <input :value="affiliate.share_url" readonly
                           class="flex-1 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 select-all">
                    <button @click="copyLink"
                            class="px-4 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition shrink-0">
                        {{ copied ? 'Copié !' : 'Copier' }}
                    </button>
                </div>
                <p class="text-xs text-slate-400 mt-2">Partagez ce lien sur WhatsApp, Facebook, Instagram… Le cookie dure 30 jours.</p>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white border border-slate-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-slate-500">Gains totaux</p>
                    <p class="text-lg font-bold text-slate-900 mt-1">{{ fmt(affiliate.total_earned) }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-slate-500">Solde disponible</p>
                    <p class="text-lg font-bold text-green-600 mt-1">{{ fmt(affiliate.pending_balance) }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-slate-500">Commandes</p>
                    <p class="text-lg font-bold text-slate-900 mt-1">{{ affiliate.commissions_count }}</p>
                </div>
            </div>

            <!-- Retrait -->
            <div class="flex items-center justify-between bg-white border border-slate-200 rounded-xl p-4">
                <div>
                    <p class="text-sm font-medium text-slate-900">Demander un retrait</p>
                    <p class="text-xs text-slate-500">Minimum {{ fmt(min_withdrawal) }}</p>
                </div>
                <button @click="requestWithdrawal" :disabled="!canWithdraw"
                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed transition">
                    Retirer {{ fmt(confirmedBalance) }}
                </button>
            </div>

            <!-- Commissions -->
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">Dernières commissions</h3>
                </div>
                <div v-if="commissions.length === 0" class="p-8 text-center">
                    <p class="text-sm text-slate-400">Aucune commission pour le moment.</p>
                </div>
                <div v-else class="divide-y divide-slate-100">
                    <div v-for="c in commissions" :key="c.id" class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-900">#{{ c.order_number }}</p>
                            <p class="text-xs text-slate-400">{{ c.created_at }} · {{ c.commission_rate }}%</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-900">{{ fmt(c.commission_amount) }}</p>
                            <span class="inline-block text-[11px] font-medium px-2 py-0.5 rounded-full" :class="statusColor(c.status)">{{ statusLabel(c.status) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Moyen de paiement -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Moyen de paiement</h3>
                <form @submit.prevent="submitPayment" class="flex gap-2">
                    <input v-model="paymentForm.payment_method" type="text" required
                           class="flex-1 px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <button type="submit" :disabled="paymentForm.processing"
                            class="px-4 py-2.5 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800 disabled:opacity-50 transition">
                        Modifier
                    </button>
                </form>
            </div>
        </div>

        <!-- Suspendu -->
        <div v-else class="max-w-lg mx-auto">
            <div class="bg-white border border-red-200 rounded-2xl p-6 text-center">
                <p class="text-sm text-red-600 font-medium">Votre compte affilié est suspendu. Contactez l'équipe pour plus d'informations.</p>
            </div>
        </div>
    </AccountLayout>
</template>
