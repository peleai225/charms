<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    balances: Array,
    totals: Object,
    startDate: String,
    endDate: String,
})

const start = ref(props.startDate)
const end   = ref(props.endDate)

function applyFilters() {
    router.get(route('admin.accounting.balance'), {
        start_date: start.value,
        end_date:   end.value,
    }, { preserveState: true, replace: true })
}

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F CFA'
}

function fmtDate(dateStr) {
    if (!dateStr) return '—'
    const d = new Date(dateStr)
    return d.toLocaleDateString('fr-FR')
}

const isBalanced = Math.abs((props.totals?.debit ?? 0) - (props.totals?.credit ?? 0)) < 0.01
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Balance générale</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">
                    Du {{ fmtDate(startDate) }} au {{ fmtDate(endDate) }}
                </p>
            </div>
            <a :href="route('admin.accounting.index')"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>

        <!-- Date filter -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-[12px] font-medium text-gray-600 mb-1.5">Date début</label>
                    <input type="date" v-model="start"
                        class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-gray-600 mb-1.5">Date fin</label>
                    <input type="date" v-model="end"
                        class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <button @click="applyFilters"
                    class="h-9 px-4 text-[13px] font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors">
                    Appliquer
                </button>
            </div>
        </div>

        <!-- Balance table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide w-28">Code</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Compte</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Débit</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Crédit</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Solde</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="!balances || balances.length === 0">
                            <td colspan="5" class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-[13px] text-gray-500">Aucune écriture sur cette période</p>
                            </td>
                        </tr>
                        <tr v-for="row in balances" :key="row.account_id"
                            class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 font-mono font-medium text-gray-600">{{ row.account_code }}</td>
                            <td class="px-5 py-3 font-medium text-gray-900">{{ row.account_name }}</td>
                            <td class="px-5 py-3 text-right text-gray-600 tabular-nums">{{ fmt(row.debit) }}</td>
                            <td class="px-5 py-3 text-right text-gray-600 tabular-nums">{{ fmt(row.credit) }}</td>
                            <td class="px-5 py-3 text-right font-semibold tabular-nums"
                                :class="row.balance >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ fmt(Math.abs(row.balance)) }}
                                <span class="text-[11px] ml-0.5">{{ row.balance < 0 ? 'C' : 'D' }}</span>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="balances && balances.length > 0"
                        class="bg-gray-100 border-t-2 border-gray-300">
                        <tr>
                            <td colspan="2" class="px-5 py-3 font-bold text-gray-900 uppercase text-[12px] tracking-wide">Total</td>
                            <td class="px-5 py-3 text-right font-bold text-gray-900 tabular-nums">{{ fmt(totals?.debit) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-gray-900 tabular-nums">{{ fmt(totals?.credit) }}</td>
                            <td class="px-5 py-3 text-right font-bold tabular-nums"
                                :class="isBalanced ? 'text-green-600' : 'text-red-600'">
                                <span v-if="isBalanced">Équilibrée</span>
                                <span v-else>Écart: {{ fmt(Math.abs((totals?.debit ?? 0) - (totals?.credit ?? 0))) }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</template>
