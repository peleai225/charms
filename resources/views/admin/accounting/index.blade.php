@extends('layouts.admin')

@section('title', 'Comptabilité')
@section('page-title', 'Tableau de bord comptabilité')

@section('content')
<div class="space-y-6">
    <!-- Filtres période -->
    <div class="flex items-center justify-between">
        <div class="flex gap-2">
            <a href="{{ route('admin.accounting.index', ['period' => 'week']) }}" 
               class="px-4 py-2 rounded-xl font-medium transition-colors {{ $period === 'week' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                Cette semaine
            </a>
            <a href="{{ route('admin.accounting.index', ['period' => 'month']) }}" 
               class="px-4 py-2 rounded-xl font-medium transition-colors {{ $period === 'month' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                Ce mois
            </a>
            <a href="{{ route('admin.accounting.index', ['period' => 'quarter']) }}" 
               class="px-4 py-2 rounded-xl font-medium transition-colors {{ $period === 'quarter' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                Ce trimestre
            </a>
            <a href="{{ route('admin.accounting.index', ['period' => 'year']) }}" 
               class="px-4 py-2 rounded-xl font-medium transition-colors {{ $period === 'year' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                Cette année
            </a>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.accounting.entries') }}" class="px-4 py-2 bg-white text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-xl font-medium transition-colors">
                Écritures comptables
            </a>
            <a href="{{ route('admin.accounting.accounts') }}" class="px-4 py-2 bg-white text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-xl font-medium transition-colors">
                Plan comptable
            </a>
        </div>
    </div>

    <!-- Stats principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Chiffre d'affaires</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1">{{ format_price($stats['revenue']) }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-xl">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Commandes payées</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['orders_count'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-xl">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Panier moyen</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1">{{ format_price($stats['average_order']) }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-xl">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Remboursements</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ format_price($stats['refunds']) }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-xl">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Graphique des revenus -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Évolution du chiffre d'affaires</h3>
            <canvas id="revenueChart" height="120"></canvas>
        </div>

        <!-- Méthodes de paiement -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Méthodes de paiement</h3>
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
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-slate-700">{{ $label }}</span>
                            <span class="text-slate-500">{{ format_price($method->total) }} ({{ $method->count }})</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Top produits -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Top 10 produits par CA</h3>
            </div>
            @if($topProducts->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($topProducts as $index => $product)
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-sm font-medium text-slate-600">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <p class="font-medium text-slate-900">{{ Str::limit($product->name, 30) }}</p>
                            <p class="text-sm text-slate-500">{{ $product->quantity_sold }} vendus</p>
                        </div>
                    </div>
                    <p class="font-semibold text-slate-900">{{ format_price($product->revenue) }}</p>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center text-slate-500">
                Aucune vente sur cette période
            </div>
            @endif
        </div>

        <!-- Dernières écritures comptables -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Dernières écritures</h3>
                <a href="{{ route('admin.accounting.entries') }}" class="text-sm text-blue-600 hover:underline">Voir tout</a>
            </div>
            @if($recentEntries->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($recentEntries as $entry)
                <div class="p-4">
                    <div class="flex items-center justify-between mb-1">
                        <p class="font-medium text-slate-900">{{ $entry->entry_number ?? $entry->document_number ?? 'N/A' }}</p>
                        <p class="font-semibold text-slate-900">{{ format_price($entry->total_debit ?? 0) }}</p>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">{{ $entry->label ?? $entry->description ?? 'N/A' }}</span>
                        <span class="text-slate-400">{{ $entry->entry_date ? $entry->entry_date->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center text-slate-500">
                Aucune écriture comptable
            </div>
            @endif
        </div>
    </div>

    <!-- Journaux comptables -->
    @if($journals->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">Journaux comptables</h3>
        </div>
        <div class="grid md:grid-cols-3 lg:grid-cols-5 gap-4 p-6">
            @foreach($journals as $journal)
            <div class="p-4 bg-slate-50 rounded-xl">
                <p class="font-medium text-slate-900">{{ $journal->name }}</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $journal->entries_count ?? 0 }}</p>
                <p class="text-sm text-slate-500">écritures</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($revenueChart['labels']),
            datasets: [{
                label: 'Chiffre d\'affaires (F CFA)',
                data: @json($revenueChart['revenues']),
                borderColor: 'rgb(37, 99, 235)',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                },
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
</script>
@endpush
@endsection
