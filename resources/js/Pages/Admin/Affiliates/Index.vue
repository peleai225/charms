<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    affiliates: Object,
    withdrawals: Object,
    filters: Object,
});

const tab = ref(props.filters?.tab || 'affiliates');
const statusFilter = ref(props.filters?.status || '');

const filterByStatus = (s) => {
    statusFilter.value = s;
    router.get('/admin/affiliates', { status: s || undefined, tab: tab.value }, { preserveState: true });
};

const switchTab = (t) => {
    tab.value = t;
    router.get('/admin/affiliates', { tab: t, status: statusFilter.value || undefined }, { preserveState: true });
};

const approve = (id) => {
    if (!confirm('Approuver cet affilié ?')) return;
    router.post(`/admin/affiliates/${id}/approve`);
};

const suspend = (id) => {
    if (!confirm('Suspendre cet affilié ?')) return;
    router.post(`/admin/affiliates/${id}/suspend`);
};

const payNote = ref('');
const showPayModal = ref(false);
const selectedWithdrawal = ref(null);

const openPayModal = (w) => {
    selectedWithdrawal.value = w;
    payNote.value = '';
    showPayModal.value = true;
};

const confirmPay = () => {
    router.post(`/admin/affiliates/withdrawals/${selectedWithdrawal.value.id}/pay`, {
        admin_note: payNote.value || null,
    }, {
        onSuccess: () => { showPayModal.value = false; },
    });
};

const rejectWithdrawal = (w) => {
    const note = prompt('Raison du rejet (optionnel) :');
    if (note === null) return;
    router.post(`/admin/affiliates/withdrawals/${w.id}/reject`, { admin_note: note || null });
};

// Drawer commissions
const drawerOpen = ref(false);
const drawerAffiliate = ref(null);
const drawerCommissions = ref([]);

const showCommissions = async (aff) => {
    drawerAffiliate.value = aff;
    drawerOpen.value = true;
    try {
        const res = await fetch(`/admin/affiliates/${aff.id}/commissions`);
        const json = await res.json();
        drawerCommissions.value = json.data || [];
    } catch {
        drawerCommissions.value = [];
    }
};

const fmt = (v) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(v) + ' F';

const statusBadge = (s) => ({
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    active: 'bg-green-50 text-green-700 border-green-200',
    suspended: 'bg-red-50 text-red-700 border-red-200',
    paid: 'bg-green-50 text-green-700 border-green-200',
    rejected: 'bg-red-50 text-red-700 border-red-200',
    confirmed: 'bg-blue-50 text-blue-700 border-blue-200',
}[s] || 'bg-slate-50 text-slate-600 border-slate-200');

const statusText = (s) => ({
    pending: 'En attente', active: 'Actif', suspended: 'Suspendu',
    paid: 'Payé', rejected: 'Rejeté', confirmed: 'Confirmé',
}[s] || s);
</script>

<template>
    <div class="space-y-5">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">Affiliations</h1>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 bg-slate-100 p-1 rounded-lg w-fit">
            <button @click="switchTab('affiliates')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition"
                    :class="tab === 'affiliates' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                Affiliés
            </button>
            <button @click="switchTab('withdrawals')"
                    class="px-4 py-2 text-sm font-medium rounded-md transition"
                    :class="tab === 'withdrawals' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                Retraits
            </button>
        </div>

        <!-- Tab Affiliés -->
        <div v-if="tab === 'affiliates'">
            <!-- Filtres -->
            <div class="flex gap-2 mb-4">
                <button v-for="s in ['', 'pending', 'active', 'suspended']" :key="s"
                        @click="filterByStatus(s)"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg border transition"
                        :class="statusFilter === s ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                    {{ s === '' ? 'Tous' : statusText(s) }}
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-slate-500">Affilié</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-500">Code</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-500">Taux</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-500">Commandes</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-500">Gains</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-500">Solde</th>
                                <th class="px-4 py-3 text-center font-medium text-slate-500">Statut</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="a in affiliates.data" :key="a.id" class="hover:bg-slate-50 cursor-pointer" @click="showCommissions(a)">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ a.user_name }}</p>
                                    <p class="text-xs text-slate-400">{{ a.user_email }}</p>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ a.code }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ a.effective_rate }}%</td>
                                <td class="px-4 py-3 text-right text-slate-700">{{ a.commissions_count }}</td>
                                <td class="px-4 py-3 text-right font-medium text-slate-900">{{ fmt(a.total_earned) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-green-600">{{ fmt(a.pending_balance) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block text-[11px] font-medium px-2 py-0.5 rounded-full border" :class="statusBadge(a.status)">{{ statusText(a.status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right" @click.stop>
                                    <button v-if="a.status === 'pending'" @click="approve(a.id)"
                                            class="text-xs text-green-600 hover:text-green-800 font-medium mr-2">Approuver</button>
                                    <button v-if="a.status === 'active'" @click="suspend(a.id)"
                                            class="text-xs text-red-600 hover:text-red-800 font-medium">Suspendre</button>
                                    <button v-if="a.status === 'suspended'" @click="approve(a.id)"
                                            class="text-xs text-green-600 hover:text-green-800 font-medium">Réactiver</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="affiliates.data.length === 0" class="p-8 text-center">
                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p class="text-sm text-slate-400">Aucun affilié</p>
                </div>
            </div>
        </div>

        <!-- Tab Retraits -->
        <div v-if="tab === 'withdrawals'">
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-slate-500">Affilié</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-500">Montant</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-500">Moyen</th>
                                <th class="px-4 py-3 text-left font-medium text-slate-500">Date</th>
                                <th class="px-4 py-3 text-center font-medium text-slate-500">Statut</th>
                                <th class="px-4 py-3 text-right font-medium text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="w in withdrawals.data" :key="w.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ w.affiliate_name }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ w.affiliate_code }}</p>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ fmt(w.amount) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ w.payment_method }}</td>
                                <td class="px-4 py-3 text-slate-500 text-xs">{{ w.created_at }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block text-[11px] font-medium px-2 py-0.5 rounded-full border" :class="statusBadge(w.status)">{{ statusText(w.status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <template v-if="w.status === 'pending'">
                                        <button @click="openPayModal(w)" class="text-xs text-green-600 hover:text-green-800 font-medium mr-2">Payer</button>
                                        <button @click="rejectWithdrawal(w)" class="text-xs text-red-600 hover:text-red-800 font-medium">Rejeter</button>
                                    </template>
                                    <span v-else-if="w.paid_at" class="text-xs text-slate-400">{{ w.paid_at }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="withdrawals.data.length === 0" class="p-8 text-center">
                    <p class="text-sm text-slate-400">Aucune demande de retrait</p>
                </div>
            </div>
        </div>

        <!-- Modal Payer -->
        <Teleport to="body">
            <div v-if="showPayModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="fixed inset-0 bg-black/40" @click="showPayModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 z-10">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Confirmer le paiement</h3>
                    <p class="text-sm text-slate-600 mb-1">Montant : <strong>{{ fmt(selectedWithdrawal?.amount) }}</strong></p>
                    <p class="text-sm text-slate-600 mb-4">Moyen : <strong>{{ selectedWithdrawal?.payment_method }}</strong></p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Note (optionnel)</label>
                        <input v-model="payNote" type="text" placeholder="Ex : Envoyé via Wave"
                               class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm">
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button @click="showPayModal = false" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Annuler</button>
                        <button @click="confirmPay" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Marquer payé</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Drawer commissions -->
        <Teleport to="body">
            <div v-if="drawerOpen" class="fixed inset-0 z-50 flex justify-end">
                <div class="fixed inset-0 bg-black/40" @click="drawerOpen = false"></div>
                <div class="relative bg-white w-full max-w-md h-full shadow-xl z-10 overflow-y-auto">
                    <div class="sticky top-0 bg-white border-b border-slate-200 px-5 py-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ drawerAffiliate?.user_name }}</h3>
                            <p class="text-xs text-slate-400 font-mono">{{ drawerAffiliate?.code }}</p>
                        </div>
                        <button @click="drawerOpen = false" class="w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-lg">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div v-if="drawerCommissions.length === 0" class="p-8 text-center">
                        <p class="text-sm text-slate-400">Aucune commission</p>
                    </div>
                    <div v-else class="divide-y divide-slate-100">
                        <div v-for="c in drawerCommissions" :key="c.id" class="px-5 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-900">#{{ c.order_number }}</p>
                                <p class="text-xs text-slate-400">{{ c.created_at }} · {{ c.commission_rate }}%</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold">{{ fmt(c.commission_amount) }}</p>
                                <span class="inline-block text-[11px] font-medium px-2 py-0.5 rounded-full border" :class="statusBadge(c.status)">{{ statusText(c.status) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
