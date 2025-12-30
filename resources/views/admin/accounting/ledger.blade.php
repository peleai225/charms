@extends('layouts.admin')

@section('title', 'Grand livre')
@section('page-title', 'Grand livre')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <a href="{{ route('admin.accounting.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-slate-700 mb-1">Compte</label>
                <select name="account_id" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                    <option value="">Sélectionner un compte</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>{{ $acc->code }} - {{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Du</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-4 py-2 border border-slate-300 rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Au</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-4 py-2 border border-slate-300 rounded-xl">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">Afficher</button>
            </div>
        </form>
    </div>

    @if($account)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">{{ $account->code }} - {{ $account->name }}</h3>
            <p class="text-sm text-slate-500">Du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Journal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Libellé</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Débit</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Crédit</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Solde</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @php $runningBalance = 0; @endphp
                    @forelse($entries as $line)
                        @php $runningBalance += $line->debit - $line->credit; @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-slate-600">{{ $line->entry->entry_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $line->entry->journal?->code ?? 'OD' }}</td>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $line->label }}</td>
                            <td class="px-6 py-4 text-right {{ $line->debit > 0 ? 'text-slate-900' : 'text-slate-400' }}">
                                {{ $line->debit > 0 ? format_price($line->debit) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right {{ $line->credit > 0 ? 'text-slate-900' : 'text-slate-400' }}">
                                {{ $line->credit > 0 ? format_price($line->credit) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ format_price(abs($runningBalance)) }} {{ $runningBalance < 0 ? 'C' : 'D' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">Aucune écriture sur ce compte</td></tr>
                    @endforelse
                </tbody>
                @if($entries->count() > 0)
                <tfoot class="bg-slate-100 border-t-2 border-slate-300">
                    <tr>
                        <td colspan="3" class="px-6 py-4 font-bold text-slate-900">SOLDE FINAL</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ format_price($entries->sum('debit')) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ format_price($entries->sum('credit')) }}</td>
                        <td class="px-6 py-4 text-right font-bold {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ format_price(abs($runningBalance)) }} {{ $runningBalance < 0 ? 'C' : 'D' }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-slate-500">Sélectionnez un compte pour afficher le grand livre.</p>
    </div>
    @endif
</div>
@endsection

