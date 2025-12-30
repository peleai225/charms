@extends('layouts.admin')

@section('title', 'Nouvelle écriture comptable')
@section('page-title', 'Nouvelle écriture comptable')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.accounting.entries') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('admin.accounting.entries.store') }}" method="POST" x-data="entryForm()">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
            <h3 class="text-lg font-semibold text-slate-900">Informations de l'écriture</h3>
            
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Journal *</label>
                    <select name="journal_id" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <option value="">Sélectionner...</option>
                        @foreach($journals as $journal)
                            <option value="{{ $journal->id }}" {{ old('journal_id') == $journal->id ? 'selected' : '' }}>
                                {{ $journal->code }} - {{ $journal->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('journal_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Date *</label>
                    <input type="date" name="entry_date" value="{{ old('entry_date', now()->format('Y-m-d')) }}" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">N° pièce</label>
                    <input type="text" name="document_number" value="{{ old('document_number') }}" placeholder="FAC-001"
                        class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Libellé *</label>
                <input type="text" name="description" value="{{ old('description') }}" required placeholder="Description de l'écriture..."
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6 mt-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Lignes d'écriture</h3>
                <button type="button" @click="addLine()" class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter une ligne
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Compte</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Libellé</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase w-36">Débit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase w-36">Crédit</th>
                            <th class="px-4 py-3 w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <template x-for="(line, index) in lines" :key="index">
                            <tr>
                                <td class="px-4 py-3">
                                    <select :name="'lines[' + index + '][account_id]'" x-model="line.account_id" required
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                        <option value="">Sélectionner...</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" :name="'lines[' + index + '][label]'" x-model="line.label" placeholder="Libellé ligne..."
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" :name="'lines[' + index + '][debit]'" x-model.number="line.debit" step="0.01" min="0" placeholder="0"
                                        @input="if(line.debit > 0) line.credit = 0"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-right focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" :name="'lines[' + index + '][credit]'" x-model.number="line.credit" step="0.01" min="0" placeholder="0"
                                        @input="if(line.credit > 0) line.debit = 0"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-right focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                </td>
                                <td class="px-4 py-3">
                                    <button type="button" @click="removeLine(index)" x-show="lines.length > 2" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-slate-50 font-semibold">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-right text-slate-600">TOTAUX</td>
                            <td class="px-4 py-3 text-right" :class="totalDebit !== totalCredit ? 'text-red-600' : 'text-slate-900'" x-text="formatPrice(totalDebit)"></td>
                            <td class="px-4 py-3 text-right" :class="totalDebit !== totalCredit ? 'text-red-600' : 'text-slate-900'" x-text="formatPrice(totalCredit)"></td>
                            <td></td>
                        </tr>
                        <tr x-show="totalDebit !== totalCredit">
                            <td colspan="5" class="px-4 py-2 text-center text-sm text-red-600">
                                ⚠️ L'écriture n'est pas équilibrée. Différence: <span x-text="formatPrice(Math.abs(totalDebit - totalCredit))"></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 mt-6">
            <a href="{{ route('admin.accounting.entries') }}" class="px-6 py-3 text-slate-600 hover:text-slate-900 font-medium">
                Annuler
            </a>
            <button type="submit" :disabled="totalDebit !== totalCredit || totalDebit === 0" 
                class="px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Enregistrer l'écriture
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function entryForm() {
    return {
        lines: [
            { account_id: '', label: '', debit: 0, credit: 0 },
            { account_id: '', label: '', debit: 0, credit: 0 },
        ],
        get totalDebit() {
            return this.lines.reduce((sum, line) => sum + (parseFloat(line.debit) || 0), 0);
        },
        get totalCredit() {
            return this.lines.reduce((sum, line) => sum + (parseFloat(line.credit) || 0), 0);
        },
        addLine() {
            this.lines.push({ account_id: '', label: '', debit: 0, credit: 0 });
        },
        removeLine(index) {
            if (this.lines.length > 2) {
                this.lines.splice(index, 1);
            }
        },
        formatPrice(value) {
            return new Intl.NumberFormat('fr-FR').format(value) + ' F CFA';
        }
    };
}
</script>
@endpush
@endsection

