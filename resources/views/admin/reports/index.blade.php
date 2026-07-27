@extends('layouts.admin')

@section('title', 'Rapports')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div>
        <h1 class="text-xl font-bold text-gray-900">Rapports & Statistiques</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">Analysez les performances de votre boutique</p>
    </div>

    {{-- Cards rapports --}}
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.reports.sales') }}"
            class="group bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow-md hover:border-blue-300 transition-all">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-[14px] font-semibold text-gray-900 mb-1 group-hover:text-blue-700 transition-colors">Rapport des ventes</h3>
            <p class="text-[12px] text-gray-500">Chiffre d'affaires, commandes et tendances</p>
            <div class="mt-3 flex items-center text-[12px] text-blue-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                Voir →
            </div>
        </a>

        <a href="{{ route('admin.reports.products') }}"
            class="group bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow-md hover:border-green-300 transition-all">
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h3 class="text-[14px] font-semibold text-gray-900 mb-1 group-hover:text-green-700 transition-colors">Rapport produits</h3>
            <p class="text-[12px] text-gray-500">Top produits, ventes par catégorie et performances</p>
            <div class="mt-3 flex items-center text-[12px] text-green-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                Voir →
            </div>
        </a>

        <a href="{{ route('admin.reports.customers') }}"
            class="group bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow-md hover:border-purple-300 transition-all">
            <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h3 class="text-[14px] font-semibold text-gray-900 mb-1 group-hover:text-purple-700 transition-colors">Rapport clients</h3>
            <p class="text-[12px] text-gray-500">Analyse des clients, fidélité et géographie</p>
            <div class="mt-3 flex items-center text-[12px] text-purple-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                Voir →
            </div>
        </a>

        <a href="{{ route('admin.reports.stock') }}"
            class="group bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow-md hover:border-amber-300 transition-all">
            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <h3 class="text-[14px] font-semibold text-gray-900 mb-1 group-hover:text-amber-700 transition-colors">Rapport stock</h3>
            <p class="text-[12px] text-gray-500">Niveaux de stock, alertes et rotation</p>
            <div class="mt-3 flex items-center text-[12px] text-amber-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                Voir →
            </div>
        </a>
    </div>

    {{-- Accès rapide --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-[13px] font-semibold text-gray-900 mb-3">Accès rapide</h3>
        <div class="grid md:grid-cols-3 gap-3">
            @foreach([
                [route('admin.accounting.index'), 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'Comptabilité'],
                [route('admin.orders.index'), 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'Commandes'],
                [route('admin.products.index'), 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'Produits'],
            ] as [$href, $icon, $label])
            <a href="{{ $href }}" class="group flex items-center gap-3 p-3.5 bg-gray-50 rounded-lg hover:bg-blue-50 hover:ring-1 hover:ring-blue-200 transition-all">
                <div class="p-1.5 bg-white rounded border border-gray-100 shadow-sm">
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                    </svg>
                </div>
                <span class="text-[13px] font-medium text-gray-700 group-hover:text-blue-700 transition-colors">{{ $label }}</span>
            </a>
            @endforeach
        </div>
    </div>

</div>
@endsection
