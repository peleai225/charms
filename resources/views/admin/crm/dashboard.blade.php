@extends('layouts.admin')
@section('title', 'CRM - Tableau de bord')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">CRM Clients</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Vue d'ensemble de votre base clients</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.crm.tags') }}" class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                Gérer les tags
            </a>
            <form method="POST" action="{{ route('admin.crm.auto-classify') }}">
                @csrf
                <button type="submit" class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Classifier automatiquement
                </button>
            </form>
        </div>
    </div>

    {{-- KPI Strip --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-100">
            @foreach([
                ['Total Clients', $totalCustomers],
                ['Clients VIP', $vipCustomers],
                ['Nouveaux (30j)', $newCustomers],
                ['Inactifs (90j+)', $inactiveCustomers],
            ] as [$label, $value])
            <div class="p-4">
                <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">{{ $label }}</p>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($value) }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Revenue KPIs --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        @foreach([
            ['Chiffre d\'affaires total', $totalRevenue],
            ['Panier moyen', $avgOrderValue],
            ['Valeur vie client moy.', $avgLifetimeValue],
        ] as [$label, $val])
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-1">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($val, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">F CFA</span></p>
        </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-12 gap-5">
        {{-- Segments --}}
        <div class="lg:col-span-4 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[13px] font-semibold text-gray-900 mb-4">Segmentation Clients</h3>
            <div class="space-y-3">
                @foreach([
                    ['VIP', $segmentData['vip'], 'bg-amber-500'],
                    ['Fidèles', $segmentData['loyal'], 'bg-blue-500'],
                    ['Nouveaux', $segmentData['new'], 'bg-green-500'],
                    ['Inactifs', $segmentData['inactive'], 'bg-red-400'],
                ] as [$seg, $cnt, $bg])
                @php $pct = $totalCustomers > 0 ? round(($cnt / $totalCustomers) * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between text-[12px] mb-1">
                        <span class="font-medium text-gray-700">{{ $seg }}</span>
                        <span class="text-gray-400">{{ $cnt }} ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="{{ $bg }} h-full rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($tags->count() > 0)
            <div class="mt-5 pt-4 border-t border-gray-100">
                <h4 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-3">Tags</h4>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($tags as $tag)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium text-white" style="background: {{ $tag->color }}">
                        {{ $tag->name }}
                        <span class="bg-black/10 px-1 rounded">{{ $tag->customers_count }}</span>
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Top clients --}}
        <div class="lg:col-span-8 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-[13px] font-semibold text-gray-900">Top 10 Clients</h3>
                <a href="{{ route('admin.customers.index') }}" class="text-[12px] text-blue-600 font-medium hover:underline">Voir tous</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Client</th>
                            <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Commandes</th>
                            <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Dépensé</th>
                            <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Panier moy.</th>
                            <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Tags</th>
                            <th class="px-5 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($topCustomers as $c)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-[11px] font-bold flex-shrink-0">{{ $c->initials }}</div>
                                    <div>
                                        <p class="text-[13px] font-medium text-gray-900">{{ $c->full_name }}</p>
                                        <p class="text-[11px] text-gray-400">{{ $c->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-[13px] font-medium text-gray-700">{{ $c->orders_count }}</td>
                            <td class="px-5 py-3 text-[13px] font-medium text-gray-900">{{ number_format($c->total_spent, 0, ',', ' ') }} F</td>
                            <td class="px-5 py-3 text-[13px] text-gray-600">{{ number_format($c->average_order_value, 0, ',', ' ') }} F</td>
                            <td class="px-5 py-3">
                                <div class="flex gap-1">
                                    @foreach($c->tags ?? [] as $tag)
                                    <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $tag->color }}" title="{{ $tag->name }}"></span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.crm.customer-analytics', $c) }}" class="text-[12px] text-blue-600 font-medium hover:underline">Analyser</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-[13px] text-gray-400">Aucun client avec des commandes</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
