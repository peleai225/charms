<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    accounts: Array,
    account:  Object,
    entries:  Array,
    startDate: String,
    endDate:   String,
    accountId: [String, Number],
})

const selectedAccount = ref(props.accountId ?? '')
const start           = ref(props.startDate)
const end             = ref(props.endDate)

function applyFilters() {
    router.get(route('admin.accounting.ledger'), {
        account_id: selectedAccount.value || undefined,
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

// Running balance per row
const rows = computed(() => {
    if (!props.entries) return []
    let running = 0
    return props.entries.map(line => {
        running += (line.debit ?? 0) - (line.credit ?? 0)
        return { ...line, running_balance: running }
    })
})

const totalDebit  = computed(() => props.entries?.reduce((s, l) => s + (l.debit ?? 0), 0) ?? 0)
const totalCredit = computed(() => props.entries?.reduce((s, l) => s + (l.credit ?? 0), 0) ?? 0)
const finalBalance = computed(() => totalDebit.value - totalCredit.value)
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Grand livre</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">
                    <template v-if="account">{{ account.code }} — {{ account.name }}</template>
                    <template v-else>Sélectionnez un compte</template>
                </p>
            </div>
            <a :href="route('admin.accounting.index')"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[12px] font-medium text-gray-600 mb-1.5">Compte</label>
                    <select v-model="selectedAccount"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Sélectionner un compte...</option>
                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                            {{ acc.code }} — {{ acc.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-gray-600 mb-1.5">Du</label>
                    <input type="date" v-model="start"
                        class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-gray-600 mb-1.5">Au</label>
                    <input type="date" v-model="end"
                        class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <button @click="applyFilters"
                    class="h-9 px-4 text-[13px] font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors">
                    Afficher
                </button>
            </div>
        </div>

        <!-- Empty state: no account selected -->
        <div v-if="!account"
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-[13px] text-gray-500">Sélectionnez un compte pour afficher le grand livre.</p>
        </div>

        <!-- Ledger table -->
        <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-[14px] font-semibold text-gray-900">{{ account.code }} — {{ account.name }}</h3>
                <p class="text-[12px] text-gray-500 mt-0.5">Du {{ fmtDate(startDate) }} au {{ fmtDate(endDate) }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Date</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Journal</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Libellé</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Débit</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Crédit</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Solde</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="rows.length === 0">
                            <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                                Aucune écriture sur ce compte pour cette période
                            </td>
                        </tr>
                        <tr v-for="line in rows" :key="line.id"
                            class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ line.entry_date_fmt }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ line.journal_code ?? 'OD' }}</td>
                            <td class="px-5 py-3 font-medium text-gray-900">{{ line.label ?? '—' }}</td>
                            <td class="px-5 py-3 text-right tabular-nums"
                                :class="line.debit > 0 ? 'text-gray-900 font-medium' : 'text-gray-300'">
                                {{ line.debit > 0 ? fmt(line.debit) : '—' }}
                            </td>
                            <td class="px-5 py-3 text-right tabular-nums"
                                :class="line.credit > 0 ? 'text-gray-900 font-medium' : 'text-gray-300'">
                                {{ line.credit > 0 ? fmt(line.credit) : '—' }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold tabular-nums"
                                :class="line.running_balance >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ fmt(Math.abs(line.running_balance)) }}
                                <span class="text-[11px] ml-0.5">{{ line.running_balance < 0 ? 'C' : 'D' }}</span>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="rows.length > 0"
                        class="bg-gray-100 border-t-2 border-gray-300">
                        <tr>
                            <td colspan="3" class="px-5 py-3 font-bold text-gray-900 uppercase text-[12px] tracking-wide">Solde final</td>
                            <td class="px-5 py-3 text-right font-bold text-gray-900 tabular-nums">{{ fmt(totalDebit) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-gray-900 tabular-nums">{{ fmt(totalCredit) }}</td>
                            <td class="px-5 py-3 text-right font-bold tabular-nums"
                                :class="finalBalance >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ fmt(Math.abs(finalBalance)) }}
                                <span class="text-[11px] ml-0.5">{{ finalBalance < 0 ? 'C' : 'D' }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</template>
