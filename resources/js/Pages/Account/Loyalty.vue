<script setup>
import AccountLayout from '@/Layouts/AccountLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    customer:     Object,
    transactions: Object,
});

const pageNumbers = computed(() => {
    const current = props.transactions.current_page;
    const last    = props.transactions.last_page;
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
    router.get('/mon-compte/fidelite', { page: p }, { preserveScroll: true });
};

const levels = [
    { min: 0,    max: 499,  label: 'Bronze',   color: 'text-amber-700 bg-amber-50 border-amber-200' },
    { min: 500,  max: 1499, label: 'Argent',   color: 'text-slate-600 bg-slate-100 border-slate-300' },
    { min: 1500, max: 2999, label: 'Or',       color: 'text-yellow-700 bg-yellow-50 border-yellow-200' },
    { min: 3000, max: Infinity, label: 'Platine', color: 'text-blue-700 bg-blue-50 border-blue-200' },
];

const currentLevel = computed(() => {
    const pts = props.customer?.points_balance || 0;
    return levels.find(l => pts >= l.min && pts <= l.max) || levels[0];
});

const nextLevel = computed(() => {
    const pts = props.customer?.points_balance || 0;
    const idx = levels.findIndex(l => pts >= l.min && pts <= l.max);
    return idx < levels.length - 1 ? levels[idx + 1] : null;
});

const progressPct = computed(() => {
    if (!nextLevel.value) return 100;
    const pts   = props.customer?.points_balance || 0;
    const start = currentLevel.value.min;
    const end   = nextLevel.value.min;
    return Math.round(((pts - start) / (end - start)) * 100);
});
</script>

<template>
    <AccountLayout title="Programme fidélité">
        <div class="mb-5">
            <h1 class="text-xl font-bold text-slate-900">Programme fidélité</h1>
            <p class="text-sm text-slate-500 mt-0.5">Gagnez des points à chaque commande</p>
        </div>

        <!-- Carte solde -->
        <div class="bg-slate-900 rounded-2xl p-6 mb-5 text-white relative overflow-hidden">
            <!-- Décor cercles (no gradient) -->
            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-8 -left-8 w-32 h-32 rounded-full bg-white/5"></div>

            <div class="relative z-10 flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-widest mb-2">Solde actuel</p>
                    <p class="text-5xl font-black tabular-nums">{{ (customer?.points_balance || 0).toLocaleString('fr-FR') }}</p>
                    <p class="text-sm text-slate-400 mt-1">points disponibles</p>
                </div>
                <span class="text-xs font-bold px-3 py-1.5 rounded-full border" :class="currentLevel.color">
                    {{ currentLevel.label }}
                </span>
            </div>

            <!-- Barre progression vers prochain niveau -->
            <div v-if="nextLevel" class="relative z-10 mt-5">
                <div class="flex justify-between text-xs text-slate-400 mb-1.5">
                    <span>{{ currentLevel.label }}</span>
                    <span>{{ nextLevel.label }} — {{ nextLevel.min.toLocaleString('fr-FR') }} pts</span>
                </div>
                <div class="h-1.5 bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-white rounded-full transition-all" :style="{ width: `${progressPct}%` }"></div>
                </div>
                <p class="text-xs text-slate-400 mt-1.5">
                    {{ (nextLevel.min - (customer?.points_balance || 0)).toLocaleString('fr-FR') }} pts pour passer {{ nextLevel.label }}
                </p>
            </div>
            <div v-else class="relative z-10 mt-4">
                <p class="text-xs text-slate-300">🏆 Vous êtes au niveau maximum !</p>
            </div>
        </div>

        <!-- Comment gagner des points -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-4">Comment gagner des points</p>
            <div class="grid sm:grid-cols-3 gap-3">
                <div v-for="tip in [
                    {icon:'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', title:'Achat', desc:'1 pt par 100 FCFA dépensés'},
                    {icon:'M5 13l4 4L19 7', title:'Commande livrée', desc:'+50 pts bonus'},
                    {icon:'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364', title:'Parrainage', desc:'+100 pts par ami'},
                ]" :key="tip.title"
                    class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl"
                >
                    <div class="w-8 h-8 bg-white border border-slate-200 rounded-lg flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="tip.icon"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-900">{{ tip.title }}</p>
                        <p class="text-xs text-slate-500">{{ tip.desc }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique transactions -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100">
                <p class="text-sm font-semibold text-slate-900">Historique des transactions</p>
            </div>

            <div v-if="!transactions?.data?.length" class="py-12 text-center">
                <p class="text-sm text-slate-500">Aucune transaction pour l'instant.</p>
            </div>

            <div v-else class="divide-y divide-slate-100">
                <div
                    v-for="tx in transactions.data"
                    :key="tx.id"
                    class="flex items-center justify-between px-5 py-4"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                            :class="tx.type === 'earn' ? 'bg-green-50' : 'bg-red-50'">
                            <svg class="w-4 h-4" :class="tx.type === 'earn' ? 'text-green-600' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tx.type === 'earn' ? 'M12 4v16m8-8H4' : 'M20 12H4'"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ tx.description || (tx.type === 'earn' ? 'Points gagnés' : 'Points utilisés') }}</p>
                            <p class="text-xs text-slate-400">{{ tx.created_at }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold tabular-nums" :class="tx.type === 'earn' ? 'text-green-600' : 'text-red-500'">
                        {{ tx.type === 'earn' ? '+' : '−' }}{{ tx.points }} pts
                    </span>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="transactions.last_page > 1" class="mt-5 flex items-center justify-center gap-1.5">
            <button :disabled="transactions.current_page === 1" @click="goToPage(transactions.current_page - 1)"
                class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg text-sm hover:bg-slate-50 disabled:opacity-40 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <template v-for="(p, i) in pageNumbers" :key="i">
                <span v-if="p === '...'" class="w-8 h-8 flex items-center justify-center text-slate-400 text-sm">…</span>
                <button v-else @click="goToPage(p)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition"
                    :class="p === transactions.current_page ? 'bg-slate-900 text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50'">
                    {{ p }}
                </button>
            </template>
            <button :disabled="transactions.current_page === transactions.last_page" @click="goToPage(transactions.current_page + 1)"
                class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg text-sm hover:bg-slate-50 disabled:opacity-40 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </AccountLayout>
</template>
