@extends('layouts.admin')

@section('title', 'Comptabilité')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header + period filter --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Comptabilité</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">Tableau de bord financier</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.accounting.index', ['period' => 'week']) }}"
               class="h-9 px-4 inline-flex items-center text-[13px] font-medium rounded-lg transition-colors {{ $period === 'week' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                Cette semaine
            </a>
            <a href="{{ route('admin.accounting.index', ['period' => 'month']) }}"
               class="h-9 px-4 inline-flex items-center text-[13px] font-medium rounded-lg transition-colors {{ $period === 'month' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                Ce mois
            </a>
            <a href="{{ route('admin.accounting.index', ['period' => 'quarter']) }}"
               class="h-9 px-4 inline-flex items-center text-[13px] font-medium rounded-lg transition-colors {{ $period === 'quarter' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                Trimestre
            </a>
            <a href="{{ route('admin.accounting.index', ['period' => 'year']) }}"
               class="h-9 px-4 inline-flex items-center text-[13px] font-medium rounded-lg transition-colors {{ $period === 'year' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
                Année
            </a>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.accounting.entries') }}" class="h-9 px-4 inline-flex items-center text-[13px] font-medium bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
            Écritures comptables
        </a>
        <a href="{{ route('admin.accounting.entries.create') }}" class="h-9 px-4 inline-flex items-center gap-1.5 text-[13px] font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle écriture
        </a>
        <a href="{{ route('admin.accounting.accounts') }}" class="h-9 px-4 inline-flex items-center text-[13px] font-medium bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
            Plan comptable
        </a>
        <a href="{{ route('admin.accounting.balance') }}" class="h-9 px-4 inline-flex items-center text-[13px] font-medium bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
            Balance générale
        </a>
        <a href="{{ route('admin.accounting.ledger') }}" class="h-9 px-4 inline-flex items-center text-[13px] font-medium bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
            Grand livre
        </a>
        <button type="button" x-data x-on:click="$dispatch('open-fec-modal')"
            class="h-9 px-4 inline-flex items-center gap-1.5 text-[13px] font-medium bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export FEC
        </button>
    </div>

    {{-- KPI strip --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-gray-100">
            <div class="p-5">
                <p class="text-[12px] font-medium text-gray-500">Chiffre d'affaires</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ format_price($stats['revenue']) }}</p>
                <div class="mt-2 w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="p-5">
                <p class="text-[12px] font-medium text-gray-500">Commandes payées</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['orders_count'] }}</p>
                <div class="mt-2 w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="p-5">
                <p class="text-[12px] font-medium text-gray-500">Panier moyen</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ format_price($stats['average_order']) }}</p>
                <div class="mt-2 w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <div class="p-5">
                <p class="text-[12px] font-medium text-gray-500">Remboursements</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ format_price($stats['refunds']) }}</p>
                <div class="mt-2 w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
        {{-- Graphique CA --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Évolution du chiffre d'affaires</h3>
            <canvas id="revenueChart" height="120"></canvas>
        </div>

        {{-- Méthodes de paiement --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Méthodes de paiement</h3>
            @if($paymentMethods->count() > 0)
                <div class="space-y-4">
                    @foreach($paymentMethods as $method)
                    @php
                        $label = match($method->payment_method) {
                            'cinetpay' => 'CinetPay',
                            'cash_on_delivery' => 'À la livraison',
                            default => ucfirst($method->payment_method ?? 'Autre'),
                        };
                        $percentage = $stats['revenue'] > 0 ? ($method->total / $stats['revenue']) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-[13px] mb-1">
                            <span class="font-medium text-gray-700">{{ $label }}</span>
                            <span class="text-gray-500">{{ format_price($method->total) }} ({{ $method->count }})</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-[13px] text-gray-400 text-center py-8">Aucune donnée</p>
            @endif
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">

        {{-- Top produits --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-[14px] font-semibold text-gray-900">Top 10 produits par CA</h3>
            </div>
            @if($topProducts->count() > 0)
            <div class="divide-y divide-gray-50">
                @foreach($topProducts as $index => $product)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center text-[11px] font-semibold text-gray-600 flex-shrink-0">{{ $index + 1 }}</span>
                        <div>
                            <p class="text-[13px] font-medium text-gray-900">{{ Str::limit($product->name, 30) }}</p>
                            <p class="text-[11px] text-gray-400">{{ $product->quantity_sold }} vendus</p>
                        </div>
                    </div>
                    <p class="text-[13px] font-semibold text-gray-900">{{ format_price($product->revenue) }}</p>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center">
                <p class="text-[13px] text-gray-400">Aucune vente sur cette période</p>
            </div>
            @endif
        </div>

        {{-- Dernières écritures --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-[14px] font-semibold text-gray-900">Dernières écritures</h3>
                <a href="{{ route('admin.accounting.entries') }}" class="text-[13px] text-blue-600 hover:underline">Voir tout</a>
            </div>
            @if($recentEntries->count() > 0)
            <div class="divide-y divide-gray-50">
                @foreach($recentEntries as $entry)
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between mb-0.5">
                        <p class="text-[13px] font-medium text-gray-900">{{ $entry->entry_number ?? $entry->document_number ?? 'N/A' }}</p>
                        <p class="text-[13px] font-semibold text-gray-900">{{ format_price($entry->total_debit ?? 0) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] text-gray-500">{{ $entry->label ?? $entry->description ?? 'N/A' }}</span>
                        <span class="text-[11px] text-gray-400">{{ $entry->entry_date ? $entry->entry_date->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center">
                <p class="text-[13px] text-gray-400">Aucune écriture comptable</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Journaux comptables --}}
    @if($journals->count() > 0)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-[14px] font-semibold text-gray-900">Journaux comptables</h3>
        </div>
        <div class="grid md:grid-cols-3 lg:grid-cols-5 gap-4 p-5">
            @foreach($journals as $journal)
            <div class="p-4 bg-gray-50 rounded-xl">
                <p class="text-[13px] font-medium text-gray-900">{{ $journal->name }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $journal->entries_count ?? 0 }}</p>
                <p class="text-[12px] text-gray-500">écritures</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- Modal Export FEC --}}
<div x-data="{ open: false }" x-on:open-fec-modal.window="open = true"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div @click.outside="open = false" class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-[15px] font-semibold text-gray-900">Export FEC / CSV</h3>
            <button @click="open = false" class="p-1.5 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.accounting.export') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Date début *</label>
                    <input type="date" name="start_date" required value="{{ now()->startOfYear()->format('Y-m-d') }}"
                        class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Date fin *</label>
                    <input type="date" name="end_date" required value="{{ now()->format('Y-m-d') }}"
                        class="w-full px-3 py-2 text-[13px] border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-[13px] font-medium text-gray-700 mb-1.5">Format</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 text-[13px] cursor-pointer">
                        <input type="radio" name="format" value="fec" checked class="accent-blue-600"> FEC (Administration fiscale)
                    </label>
                    <label class="flex items-center gap-2 text-[13px] cursor-pointer">
                        <input type="radio" name="format" value="csv" class="accent-blue-600"> CSV simple
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="open = false" class="h-9 px-4 text-[13px] font-medium text-gray-600 hover:bg-gray-50 border border-gray-200 rounded-lg">
                    Annuler
                </button>
                <button type="submit" class="h-9 px-4 text-[13px] font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg">
                    Télécharger
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        const existingChart = Chart.getChart(ctx);
        if (existingChart) existingChart.destroy();

        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($revenueChart['labels']),
                datasets: [{
                    label: 'Chiffre d\'affaires (F CFA)',
                    data: @json($revenueChart['revenues']),
                    borderColor: 'rgb(37, 99, 235)',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' F CFA';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection
