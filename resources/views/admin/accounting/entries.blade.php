@extends('layouts.admin')

@section('title', 'Écritures comptables')
@section('page-title', 'Écritures comptables')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.accounting.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour au tableau de bord
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <select name="journal" class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Tous les journaux</option>
                @foreach($journals as $journal)
                    <option value="{{ $journal->id }}" {{ request('journal') == $journal->id ? 'selected' : '' }}>
                        {{ $journal->name }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                Filtrer
            </button>
            @if(request()->hasAny(['journal', 'start_date', 'end_date']))
                <a href="{{ route('admin.accounting.entries') }}" class="px-4 py-2 text-slate-600 hover:text-slate-800">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Référence</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Journal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Description</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Débit</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase">Crédit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($entries as $entry)
                        <tr class="hover:bg-slate-50 cursor-pointer" onclick="window.location='{{ route('admin.accounting.entries.show', $entry) }}'">
                            <td class="px-6 py-4 text-slate-600">{{ $entry->entry_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $entry->reference }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-700">
                                    {{ $entry->journal->code ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ Str::limit($entry->description, 50) }}</td>
                            <td class="px-6 py-4 text-right font-medium text-slate-900">{{ format_price($entry->total_debit) }}</td>
                            <td class="px-6 py-4 text-right font-medium text-slate-900">{{ format_price($entry->total_credit) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Aucune écriture comptable
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

