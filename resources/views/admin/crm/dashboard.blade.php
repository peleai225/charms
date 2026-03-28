@extends('layouts.admin')
@section('title', 'CRM - Tableau de bord')
@section('page-title', 'CRM Clients')

@section('content')
<div class="space-y-6">

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total Clients', $totalCustomers, 'from-blue-500 to-cyan-500', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['Clients VIP', $vipCustomers, 'from-amber-500 to-orange-500', 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
            ['Nouveaux (30j)', $newCustomers, 'from-emerald-500 to-green-500', 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
            ['Inactifs (90j+)', $inactiveCustomers, 'from-red-500 to-rose-500', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as [$label, $value, $gradient, $icon])
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ number_format($value) }}</p>
            <p class="text-xs text-slate-400 font-medium mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Revenue KPIs --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
            <p class="text-sm text-slate-400 font-medium">Chiffre d'affaires total</p>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ number_format($totalRevenue, 0, ',', ' ') }} <span class="text-sm text-slate-400">F CFA</span></p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
            <p class="text-sm text-slate-400 font-medium">Panier moyen</p>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ number_format($avgOrderValue, 0, ',', ' ') }} <span class="text-sm text-slate-400">F CFA</span></p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
            <p class="text-sm text-slate-400 font-medium">Valeur vie client moy.</p>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ number_format($avgLifetimeValue, 0, ',', ' ') }} <span class="text-sm text-slate-400">F CFA</span></p>
        </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-6">
        {{-- Segments --}}
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
            <h3 class="font-bold text-slate-900 mb-4">Segmentation Clients</h3>
            <div class="space-y-3">
                @foreach([
                    ['VIP', $segmentData['vip'], 'bg-amber-500'],
                    ['Fideles', $segmentData['loyal'], 'bg-emerald-500'],
                    ['Nouveaux', $segmentData['new'], 'bg-blue-500'],
                    ['Inactifs', $segmentData['inactive'], 'bg-red-500'],
                ] as [$seg, $cnt, $bg])
                @php $pct = $totalCustomers > 0 ? round(($cnt / $totalCustomers) * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-semibold text-slate-700">{{ $seg }}</span>
                        <span class="text-slate-400">{{ $cnt }} ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="{{ $bg }} h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <h4 class="font-semibold text-sm text-slate-700 mb-3">Tags</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold text-white" style="background: {{ $tag->color }}">
                        {{ $tag->name }}
                        <span class="bg-white/20 px-1 rounded">{{ $tag->customers_count }}</span>
                    </span>
                    @endforeach
                </div>
                <a href="{{ route('admin.crm.tags') }}" class="mt-3 inline-flex text-xs text-indigo-600 font-semibold hover:underline">Gerer les tags</a>
            </div>

            <form method="POST" action="{{ route('admin.crm.auto-classify') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full py-2 bg-indigo-50 text-indigo-700 font-bold text-sm rounded-xl hover:bg-indigo-100 transition-colors">
                    Classifier automatiquement
                </button>
            </form>
        </div>

        {{-- Top clients --}}
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900">Top 10 Clients</h3>
                <a href="{{ route('admin.customers.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Voir tous</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Client</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Commandes</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Depense totale</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Panier moyen</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tags</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($topCustomers as $c)
                        <tr class="hover:bg-indigo-50/20 transition-colors">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">{{ $c->initials }}</div>
                                    <div>
                                        <p class="font-semibold text-sm text-slate-900">{{ $c->full_name }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $c->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-sm font-bold text-slate-700">{{ $c->orders_count }}</td>
                            <td class="px-6 py-3 text-sm font-bold text-slate-900">{{ number_format($c->total_spent, 0, ',', ' ') }} F</td>
                            <td class="px-6 py-3 text-sm text-slate-600">{{ number_format($c->average_order_value, 0, ',', ' ') }} F</td>
                            <td class="px-6 py-3">
                                <div class="flex gap-1">
                                    @foreach($c->tags ?? [] as $tag)
                                    <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $tag->color }}" title="{{ $tag->name }}"></span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('admin.crm.customer-analytics', $c) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold">Analyser</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
