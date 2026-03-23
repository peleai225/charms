@extends('layouts.admin')

@section('title', 'Plan comptable')
@section('page-title', 'Plan comptable')

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

    @php
        $typeLabels = [
            'asset' => ['name' => 'Actifs', 'color' => 'blue'],
            'liability' => ['name' => 'Passifs', 'color' => 'red'],
            'equity' => ['name' => 'Capitaux propres', 'color' => 'purple'],
            'revenue' => ['name' => 'Produits', 'color' => 'green'],
            'expense' => ['name' => 'Charges', 'color' => 'amber'],
        ];
    @endphp

    <!-- Comptes par type -->
    @foreach($accounts as $type => $typeAccounts)
    @php
        $typeInfo = $typeLabels[$type] ?? ['name' => ucfirst($type), 'color' => 'slate'];
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-100 flex items-center gap-3">
            <span class="w-3 h-3 rounded-full
                @switch($type)
                    @case('asset') bg-blue-500 @break
                    @case('liability') bg-red-500 @break
                    @case('equity') bg-purple-500 @break
                    @case('revenue') bg-green-500 @break
                    @case('expense') bg-amber-500 @break
                    @default bg-slate-500
                @endswitch"></span>
            <h2 class="text-lg font-semibold text-slate-900">{{ $typeInfo['name'] }}</h2>
            <span class="text-sm text-slate-500">({{ $typeAccounts->count() }} comptes)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Libellé</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Solde</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($typeAccounts as $account)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <span class="font-mono font-medium text-slate-900">{{ $account->code }}</span>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $account->name }}</td>
                            <td class="px-6 py-4 text-slate-600 text-sm">{{ $account->description }}</td>
                            <td class="px-6 py-4 text-right">
                                @php
                                    $balance = $account->balance ?? 0;
                                @endphp
                                <span class="font-medium {{ $balance >= 0 ? 'text-slate-900' : 'text-red-600' }}">
                                    {{ format_price(abs($balance)) }}
                                    @if($balance < 0) <span class="text-red-500">(C)</span> @endif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    @if($accounts->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Plan comptable vide</h3>
        <p class="text-slate-600">Aucun compte comptable n'a été configuré.</p>
    </div>
    @endif
</div>
@endsection

