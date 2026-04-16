@extends('layouts.admin')

@section('title', 'Écriture ' . $entry->reference)
@section('page-title', 'Détail de l\'écriture')

@section('content')
<div class="space-y-6">

    {{-- Retour --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.accounting.entries') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-medium transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux écritures
        </a>
        <a href="{{ route('admin.accounting.entries.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle écriture
        </a>
    </div>

    {{-- En-tête de l'écriture --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Référence</p>
                <p class="text-lg font-mono font-bold text-slate-900">{{ $entry->reference }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Date</p>
                <p class="text-lg font-semibold text-slate-900">{{ $entry->entry_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Journal</p>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100">
                    {{ $entry->journal->code ?? '—' }} — {{ $entry->journal->name ?? '—' }}
                </span>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Équilibre</p>
                @if($entry->isBalanced())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Équilibrée
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold rounded-xl bg-red-50 text-red-700 ring-1 ring-red-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Déséquilibrée
                    </span>
                @endif
            </div>
        </div>

        @if($entry->description)
        <div class="mt-6 pt-6 border-t border-slate-100">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Description</p>
            <p class="text-slate-700">{{ $entry->description }}</p>
        </div>
        @endif

        @if($entry->order)
        <div class="mt-4 p-4 bg-amber-50 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span class="text-sm text-amber-800">Liée à la commande</span>
            <a href="{{ route('admin.orders.show', $entry->order) }}" class="text-sm font-bold text-amber-900 hover:underline">
                {{ $entry->order->order_number }}
            </a>
        </div>
        @endif
    </div>

    {{-- Lignes comptables --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900">Lignes d'écriture</h3>
            <span class="text-sm text-slate-500">{{ $entry->lines->count() }} ligne(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Compte</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Libellé</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Débit</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Crédit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($entry->lines as $line)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-sm font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg">{{ $line->account->code ?? '—' }}</span>
                                <span class="text-sm text-slate-600">{{ $line->account->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">{{ $line->label ?: '—' }}</td>
                        <td class="px-6 py-4 text-right">
                            @if($line->debit > 0)
                                <span class="font-semibold text-slate-900">{{ format_price($line->debit) }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($line->credit > 0)
                                <span class="font-semibold text-slate-900">{{ format_price($line->credit) }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-sm font-bold text-slate-700 text-right uppercase tracking-wider">Totaux</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ format_price($entry->total_debit) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ format_price($entry->total_credit) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Métadonnées --}}
    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4 flex flex-wrap gap-6 text-sm text-slate-500">
        <div>
            <span class="font-medium text-slate-700">Créée le : </span>
            {{ $entry->created_at->format('d/m/Y à H:i') }}
        </div>
        @if($entry->createdBy)
        <div>
            <span class="font-medium text-slate-700">Par : </span>
            {{ $entry->createdBy->name }}
        </div>
        @endif
        @if($entry->fiscal_year)
        <div>
            <span class="font-medium text-slate-700">Exercice : </span>
            {{ $entry->fiscal_year }}
        </div>
        @endif
    </div>

</div>
@endsection
