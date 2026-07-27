<script setup>
const props = defineProps({
    accounts: Object, // grouped by type: { asset: [...], liability: [...], ... }
})

const TYPE_LABELS = {
    asset: 'Actifs',
    liability: 'Passifs',
    equity: 'Capitaux propres',
    revenue: 'Produits',
    expense: 'Charges',
}

const TYPE_COLORS = {
    asset: 'bg-blue-500',
    liability: 'bg-red-500',
    equity: 'bg-purple-500',
    revenue: 'bg-green-500',
    expense: 'bg-amber-500',
}

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F CFA'
}

function typeLabel(type) {
    return TYPE_LABELS[type] ?? (type ? type.charAt(0).toUpperCase() + type.slice(1) : '—')
}

function typeColor(type) {
    return TYPE_COLORS[type] ?? 'bg-gray-500'
}

const totalAccounts = Object.values(props.accounts ?? {}).reduce((sum, arr) => sum + arr.length, 0)
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Plan comptable</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">{{ totalAccounts }} compte(s) au total</p>
            </div>
            <a :href="route('admin.accounting.index')"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>

        <!-- Empty state -->
        <div v-if="totalAccounts === 0"
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-[15px] font-semibold text-gray-900 mb-1">Plan comptable vide</h3>
            <p class="text-[13px] text-gray-500">Aucun compte comptable n'a été configuré.</p>
        </div>

        <!-- Per-type sections -->
        <div v-for="(typeAccounts, type) in accounts" :key="type"
            class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <!-- Section header -->
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                <span class="w-3 h-3 rounded-full flex-shrink-0" :class="typeColor(type)"></span>
                <h2 class="text-[14px] font-semibold text-gray-900">{{ typeLabel(type) }}</h2>
                <span class="text-[13px] text-gray-400">({{ typeAccounts.length }} comptes)</span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide w-28">Code</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Libellé</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Description</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Solde</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="account in typeAccounts" :key="account.id"
                            class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <span class="font-mono font-semibold text-gray-900">{{ account.code }}</span>
                            </td>
                            <td class="px-5 py-3 font-medium text-gray-900">{{ account.name }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ account.description ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <span :class="account.balance < 0 ? 'text-red-600' : 'text-gray-900'"
                                    class="font-medium tabular-nums">
                                    {{ fmt(Math.abs(account.balance ?? 0)) }}
                                    <span v-if="account.balance < 0" class="text-red-400 text-[11px]">(C)</span>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
