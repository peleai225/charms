@extends('layouts.admin')

@section('title', 'Rapports')
@section('page-title', 'Rapports et statistiques')

@section('content')
<div class="space-y-6">
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Rapport Ventes -->
        <a href="{{ route('admin.reports.sales') }}" class="group bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-lg hover:border-blue-300 transition-all">
            <div class="p-4 bg-blue-100 rounded-2xl w-fit mb-4 group-hover:bg-blue-200 transition-colors">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">Rapport des ventes</h3>
            <p class="text-slate-500 text-sm">Analyse du chiffre d'affaires, commandes et tendances</p>
        </a>

        <!-- Rapport Produits -->
        <a href="{{ route('admin.reports.products') }}" class="group bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-lg hover:border-green-300 transition-all">
            <div class="p-4 bg-green-100 rounded-2xl w-fit mb-4 group-hover:bg-green-200 transition-colors">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">Rapport produits</h3>
            <p class="text-slate-500 text-sm">Top produits, ventes par catégorie et performances</p>
        </a>

        <!-- Rapport Clients -->
        <a href="{{ route('admin.reports.customers') }}" class="group bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-lg hover:border-purple-300 transition-all">
            <div class="p-4 bg-purple-100 rounded-2xl w-fit mb-4 group-hover:bg-purple-200 transition-colors">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">Rapport clients</h3>
            <p class="text-slate-500 text-sm">Analyse des clients, fidélité et géographie</p>
        </a>

        <!-- Rapport Stock -->
        <a href="{{ route('admin.reports.stock') }}" class="group bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-lg hover:border-amber-300 transition-all">
            <div class="p-4 bg-amber-100 rounded-2xl w-fit mb-4 group-hover:bg-amber-200 transition-colors">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">Rapport stock</h3>
            <p class="text-slate-500 text-sm">Niveaux de stock, alertes et rotation</p>
        </a>
    </div>

    <!-- Raccourcis -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Accès rapide</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <a href="{{ route('admin.accounting.index') }}" class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors">
                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span class="font-medium text-slate-700">Comptabilité</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors">
                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="font-medium text-slate-700">Commandes</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors">
                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="font-medium text-slate-700">Produits</span>
            </a>
        </div>
    </div>
</div>
@endsection

