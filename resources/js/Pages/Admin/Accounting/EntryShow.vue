<script setup>
const props = defineProps({
    entry: Object,
})

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F CFA'
}
</script>

<template>
    <div class="p-6 space-y-5 max-w-4xl">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a :href="route('admin.accounting.entries')"
                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
                <h1 class="text-xl font-bold text-gray-900">
                    Écriture {{ entry.entry_number ?? '—' }}
                </h1>
            </div>
            <a :href="route('admin.accounting.entries.create')"
                class="h-9 px-4 inline-flex items-center gap-1.5 text-[13px] font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle écriture
            </a>
        </div>

        <!-- Entry header card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-1">Référence</p>
                    <p class="text-[15px] font-mono font-bold text-gray-900">{{ entry.entry_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-1">Date</p>
                    <p class="text-[15px] font-semibold text-gray-900">{{ entry.entry_date_fmt }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-1">Journal</p>
                    <span class="inline-flex items-center px-2.5 py-1 text-[12px] font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-100">
                        {{ entry.journal_code ?? '—' }} — {{ entry.journal_name ?? '—' }}
                    </span>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-1">Équilibre</p>
                    <span v-if="entry.is_balanced"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[12px] font-semibold rounded-lg bg-green-50 text-green-700 border border-green-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Équilibrée
                    </span>
                    <span v-else
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[12px] font-semibold rounded-lg bg-red-50 text-red-700 border border-red-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Déséquilibrée
                    </span>
                </div>
            </div>

            <div v-if="entry.description" class="mt-5 pt-5 border-t border-gray-100">
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-1">Description</p>
                <p class="text-[13px] text-gray-700">{{ entry.description }}</p>
            </div>

            <!-- Linked order -->
            <div v-if="entry.order_number" class="mt-4 p-3 bg-amber-50 rounded-lg flex items-center gap-3 border border-amber-100">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-[13px] text-amber-800">Liée à la commande</span>
                <a :href="route('admin.orders.show', entry.order_id)"
                    class="text-[13px] font-bold text-amber-900 hover:underline">
                    {{ entry.order_number }}
                </a>
            </div>
        </div>

        <!-- Lines table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-[14px] font-semibold text-gray-900">Lignes d'écriture</h3>
                <span class="text-[12px] text-gray-400">{{ entry.lines?.length ?? 0 }} ligne(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Compte</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Libellé</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Débit</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Crédit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="line in entry.lines" :key="line.id"
                            class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-[12px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg">
                                        {{ line.account_code ?? '—' }}
                                    </span>
                                    <span class="text-gray-600">{{ line.account_name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ line.label ?? '—' }}</td>
                            <td class="px-5 py-3 text-right tabular-nums">
                                <span v-if="line.debit > 0" class="font-semibold text-gray-900">{{ fmt(line.debit) }}</span>
                                <span v-else class="text-gray-300">—</span>
                            </td>
                            <td class="px-5 py-3 text-right tabular-nums">
                                <span v-if="line.credit > 0" class="font-semibold text-gray-900">{{ fmt(line.credit) }}</span>
                                <span v-else class="text-gray-300">—</span>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr>
                            <td colspan="2" class="px-5 py-3 text-right font-bold text-gray-700 uppercase text-[12px] tracking-wide">
                                Totaux
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-gray-900 tabular-nums">{{ fmt(entry.total_debit) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-gray-900 tabular-nums">{{ fmt(entry.total_credit) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Metadata -->
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 flex flex-wrap gap-6 text-[13px] text-gray-500">
            <div v-if="entry.created_at_fmt">
                <span class="font-medium text-gray-700">Créée le :</span>
                {{ entry.created_at_fmt }}
            </div>
            <div v-if="entry.created_by_name">
                <span class="font-medium text-gray-700">Par :</span>
                {{ entry.created_by_name }}
            </div>
            <div v-if="entry.fiscal_year">
                <span class="font-medium text-gray-700">Exercice :</span>
                {{ entry.fiscal_year }}
            </div>
            <div v-if="entry.document_number">
                <span class="font-medium text-gray-700">N° pièce :</span>
                {{ entry.document_number }}
            </div>
        </div>

    </div>
</template>
