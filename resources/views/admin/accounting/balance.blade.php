@extends('layouts.admin')

@section('title', 'Balance générale')
@section('page-title', 'Balance générale')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.accounting.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>

        <form method="GET" class="flex gap-3">
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-4 py-2 border border-slate-300 rounded-xl">
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-4 py-2 border border-slate-300 rounded-xl">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">Appliquer</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">Balance des comptes</h3>
            <p class="text-sm text-slate-500">Du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Code</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Compte</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Débit</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Crédit</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Solde</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($balances as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 font-mono text-slate-600">{{ $row['account']->code }}</td>
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $row['account']->name }}</td>
                        <td class="px-6 py-4 text-right text-slate-600">{{ format_price($row['debit']) }}</td>
                        <td class="px-6 py-4 text-right text-slate-600">{{ format_price($row['credit']) }}</td>
                        <td class="px-6 py-4 text-right font-semibold {{ $row['balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ format_price(abs($row['balance'])) }} {{ $row['balance'] < 0 ? 'C' : 'D' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">Aucune écriture sur cette période</td></tr>
                    @endforelse
                </tbody>
                @if(count($balances) > 0)
                <tfoot class="bg-slate-100 border-t-2 border-slate-300">
                    <tr>
                        <td colspan="2" class="px-6 py-4 font-bold text-slate-900">TOTAL</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ format_price($totals['debit']) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ format_price($totals['credit']) }}</td>
                        <td class="px-6 py-4 text-right font-bold {{ abs($totals['debit'] - $totals['credit']) < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                            @if(abs($totals['debit'] - $totals['credit']) < 0.01)
                                ✓ Équilibrée
                            @else
                                Écart: {{ format_price(abs($totals['debit'] - $totals['credit'])) }}
                            @endif
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

