<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    entries: Object,
    journals: Array,
    filters: Object,
})

const journal   = ref(props.filters?.journal ?? '')
const startDate = ref(props.filters?.start_date ?? '')
const endDate   = ref(props.filters?.end_date ?? '')

function applyFilters() {
    router.get(route('admin.accounting.entries'), {
        journal:    journal.value || undefined,
        start_date: startDate.value || undefined,
        end_date:   endDate.value || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    journal.value   = ''
    startDate.value = ''
    endDate.value   = ''
    applyFilters()
}

const hasFilters = computed(() => journal.value || startDate.value || endDate.value)

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F CFA'
}
</script>

<template>
    <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Écritures comptables</h1>
                <p class="text-[13px] text-gray-500 mt-0.5">{{ entries.total }} écriture(s) au total</p>
            </div>
            <div class="flex gap-2">
                <a :href="route('admin.accounting.index')"
                    class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
                <a :href="route('admin.accounting.entries.create')"
                    class="h-9 px-4 inline-flex items-center gap-1.5 text-[13px] font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle écriture
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex flex-wrap gap-3">
                <select v-model="journal"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Tous les journaux</option>
                    <option v-for="j in journals" :key="j.id" :value="j.id">{{ j.name }}</option>
                </select>
                <input type="date" v-model="startDate"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <input type="date" v-model="endDate"
                    class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <button @click="applyFilters"
                    class="h-9 px-4 text-[13px] font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors">
                    Filtrer
                </button>
                <button v-if="hasFilters" @click="resetFilters"
                    class="h-9 px-3 flex items-center text-[13px] text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Date</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Référence</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Journal</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Description</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Débit</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Crédit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="entries.data.length === 0">
                            <td colspan="6" class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-[13px] font-medium text-gray-500">Aucune écriture comptable</p>
                                <a :href="route('admin.accounting.entries.create')"
                                    class="inline-flex items-center gap-1.5 mt-3 text-[13px] font-medium text-blue-600 hover:underline">
                                    Créer une écriture
                                </a>
                            </td>
                        </tr>
                        <tr v-for="entry in entries.data" :key="entry.id"
                            class="hover:bg-gray-50 cursor-pointer transition-colors group"
                            @click="router.visit(route('admin.accounting.entries.show', entry.id))">
                            <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ entry.entry_date_fmt }}</td>
                            <td class="px-5 py-3 font-medium text-gray-900 group-hover:text-blue-600 transition-colors">
                                {{ entry.entry_number ?? '—' }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full bg-gray-100 text-gray-700">
                                    {{ entry.journal_code ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 max-w-xs truncate">{{ entry.description }}</td>
                            <td class="px-5 py-3 text-right font-medium text-gray-900 tabular-nums">{{ fmt(entry.total_debit) }}</td>
                            <td class="px-5 py-3 text-right font-medium text-gray-900 tabular-nums">{{ fmt(entry.total_credit) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="entries.last_page > 1" class="px-5 py-3 border-t border-gray-100 flex items-center justify-between gap-4">
                <p class="text-[12px] text-gray-500">
                    Page {{ entries.current_page }} / {{ entries.last_page }}
                    &nbsp;·&nbsp; {{ entries.total }} résultats
                </p>
                <div class="flex items-center gap-1">
                    <a v-if="entries.prev_page_url" :href="entries.prev_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                        ← Précédent
                    </a>
                    <template v-for="link in entries.links" :key="link.label">
                        <a v-if="link.url && !link.label.includes('Suivant') && !link.label.includes('Précédent')"
                            :href="link.url"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                            class="h-8 w-8 flex items-center justify-center text-[12px] font-medium border rounded-lg transition-colors">
                            <span v-html="link.label"></span>
                        </a>
                    </template>
                    <a v-if="entries.next_page_url" :href="entries.next_page_url"
                        class="h-8 px-3 flex items-center text-[12px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                        Suivant →
                    </a>
                </div>
            </div>
        </div>

    </div>
</template>
