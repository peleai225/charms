<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    journals: Array,
    accounts: Array,
})

const today = new Date().toISOString().split('T')[0]

const form = useForm({
    journal_id:      '',
    entry_date:      today,
    document_number: '',
    description:     '',
    lines: [
        { account_id: '', label: '', debit: '', credit: '' },
        { account_id: '', label: '', debit: '', credit: '' },
    ],
})

function addLine() {
    form.lines.push({ account_id: '', label: '', debit: '', credit: '' })
}

function removeLine(index) {
    if (form.lines.length > 2) {
        form.lines.splice(index, 1)
    }
}

function onDebitInput(line) {
    if (parseFloat(line.debit) > 0) line.credit = ''
}

function onCreditInput(line) {
    if (parseFloat(line.credit) > 0) line.debit = ''
}

const totalDebit  = computed(() => form.lines.reduce((sum, l) => sum + (parseFloat(l.debit) || 0), 0))
const totalCredit = computed(() => form.lines.reduce((sum, l) => sum + (parseFloat(l.credit) || 0), 0))
const isBalanced  = computed(() => Math.abs(totalDebit.value - totalCredit.value) < 0.01)
const canSubmit   = computed(() => isBalanced.value && totalDebit.value > 0 && !form.processing)

function fmt(n) {
    return Number(n ?? 0).toLocaleString('fr-FR') + ' F CFA'
}

function submit() {
    form.post(route('admin.accounting.entries.store'), {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <div class="p-6 space-y-5 max-w-4xl">

        <!-- Header -->
        <div class="flex items-center gap-4">
            <a :href="route('admin.accounting.entries')"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Nouvelle écriture</h1>
            </div>
        </div>

        <!-- Global error -->
        <div v-if="form.errors.general"
            class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-[13px]">
            {{ form.errors.general }}
        </div>

        <!-- Entry info card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
            <h3 class="text-[14px] font-semibold text-gray-900">Informations de l'écriture</h3>

            <div class="grid md:grid-cols-3 gap-4">
                <!-- Journal -->
                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Journal *</label>
                    <select v-model="form.journal_id" required
                        :class="form.errors.journal_id ? 'border-red-400' : 'border-gray-200'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Sélectionner...</option>
                        <option v-for="j in journals" :key="j.id" :value="j.id">
                            {{ j.code }} — {{ j.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.journal_id" class="mt-1 text-[12px] text-red-600">{{ form.errors.journal_id }}</p>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Date *</label>
                    <input type="date" v-model="form.entry_date" required
                        :class="form.errors.entry_date ? 'border-red-400' : 'border-gray-200'"
                        class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <p v-if="form.errors.entry_date" class="mt-1 text-[12px] text-red-600">{{ form.errors.entry_date }}</p>
                </div>

                <!-- Document number -->
                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">N° pièce</label>
                    <input type="text" v-model="form.document_number" placeholder="FAC-001"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Libellé *</label>
                <input type="text" v-model="form.description" required placeholder="Description de l'écriture..."
                    :class="form.errors.description ? 'border-red-400' : 'border-gray-200'"
                    class="w-full h-9 px-3 text-[13px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <p v-if="form.errors.description" class="mt-1 text-[12px] text-red-600">{{ form.errors.description }}</p>
            </div>
        </div>

        <!-- Lines card -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-[14px] font-semibold text-gray-900">Lignes d'écriture</h3>
                <button type="button" @click="addLine"
                    class="h-8 px-3 inline-flex items-center gap-1.5 text-[12px] font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter une ligne
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 border-y border-gray-100">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Compte</th>
                            <th class="px-4 py-2.5 text-left font-semibold text-gray-600 uppercase text-[11px] tracking-wide">Libellé ligne</th>
                            <th class="px-4 py-2.5 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide w-36">Débit</th>
                            <th class="px-4 py-2.5 text-right font-semibold text-gray-600 uppercase text-[11px] tracking-wide w-36">Crédit</th>
                            <th class="px-4 py-2.5 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="(line, index) in form.lines" :key="index">
                            <td class="px-4 py-2">
                                <select v-model="line.account_id" required
                                    class="w-full h-8 px-2 border border-gray-200 rounded-lg text-[12px] focus:outline-none focus:ring-2 focus:ring-blue-600">
                                    <option value="">Sélectionner...</option>
                                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                                        {{ acc.code }} — {{ acc.name }}
                                    </option>
                                </select>
                            </td>
                            <td class="px-4 py-2">
                                <input type="text" v-model="line.label" placeholder="Libellé..."
                                    class="w-full h-8 px-2 border border-gray-200 rounded-lg text-[12px] focus:outline-none focus:ring-2 focus:ring-blue-600">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" v-model="line.debit" step="0.01" min="0" placeholder="0"
                                    @input="onDebitInput(line)"
                                    class="w-full h-8 px-2 border border-gray-200 rounded-lg text-[12px] text-right focus:outline-none focus:ring-2 focus:ring-blue-600">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" v-model="line.credit" step="0.01" min="0" placeholder="0"
                                    @input="onCreditInput(line)"
                                    class="w-full h-8 px-2 border border-gray-200 rounded-lg text-[12px] text-right focus:outline-none focus:ring-2 focus:ring-blue-600">
                            </td>
                            <td class="px-4 py-2">
                                <button type="button" @click="removeLine(index)"
                                    v-show="form.lines.length > 2"
                                    class="w-7 h-7 flex items-center justify-center text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-100">
                        <tr>
                            <td colspan="2" class="px-4 py-2.5 text-right text-[12px] font-semibold text-gray-600 uppercase tracking-wide">
                                Totaux
                            </td>
                            <td class="px-4 py-2.5 text-right font-bold tabular-nums"
                                :class="!isBalanced && totalDebit > 0 ? 'text-red-600' : 'text-gray-900'">
                                {{ fmt(totalDebit) }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-bold tabular-nums"
                                :class="!isBalanced && totalCredit > 0 ? 'text-red-600' : 'text-gray-900'">
                                {{ fmt(totalCredit) }}
                            </td>
                            <td></td>
                        </tr>
                        <tr v-if="!isBalanced && totalDebit > 0">
                            <td colspan="5" class="px-4 py-2 text-center text-[12px] text-red-600 bg-red-50">
                                L'écriture n'est pas équilibrée — différence :
                                {{ fmt(Math.abs(totalDebit - totalCredit)) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a :href="route('admin.accounting.entries')"
                class="h-9 px-4 text-[13px] font-medium text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
                Annuler
            </a>
            <button type="button" @click="submit" :disabled="!canSubmit"
                class="h-9 px-5 text-[13px] font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="form.processing">Enregistrement…</span>
                <span v-else>Enregistrer l'écriture</span>
            </button>
        </div>

    </div>
</template>
