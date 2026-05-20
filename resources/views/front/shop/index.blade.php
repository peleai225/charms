@extends('layouts.front')

@section('title', 'Boutique')
@section('meta_description', 'Découvrez notre sélection de produits de qualité. Livraison rapide en Côte d\'Ivoire. Paiement sécurisé par Mobile Money et carte bancaire.')

@section('content')
@php
    $activeFilters = collect([
        'search' => request('search'),
        'category' => request('category'),
        'color' => request('color'),
        'min_price' => request('min_price'),
        'max_price' => request('max_price'),
    ])->filter()->count();
@endphp

{{-- ═══════════════════════════════════════════════
     HERO BOUTIQUE — Compact + Trust signals
═══════════════════════════════════════════════ --}}
<section class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-primary-950 text-white overflow-hidden">
    <div class="absolute -top-16 -right-16 w-64 h-64 bg-primary-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-16 -left-16 w-72 h-72 bg-violet-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 sm:px-6 py-7 md:py-10 relative">
        <nav class="text-xs sm:text-sm text-slate-400 mb-3 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Accueil
            </a>
            <span class="text-slate-600">/</span>
            <span class="text-white font-medium">Boutique</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-black tracking-tight">Notre Boutique</h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-1">{{ $products->total() }} produits disponibles</p>
            </div>
            {{-- Trust signals --}}
            <div class="hidden md:flex items-center gap-5 text-xs text-slate-300">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Livraison 24-48h</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Paiement sécurisé</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Retours 30 jours</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     CHIPS CATÉGORIES — Scroll horizontal mobile
═══════════════════════════════════════════════ --}}
@if($categories->count() > 0)
<section class="bg-white border-b border-slate-100 sticky top-0 z-30 backdrop-blur-sm bg-white/95">
    <div class="container mx-auto px-4 sm:px-6 py-3">
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-none -mx-1 px-1" style="scrollbar-width: none;">
            <a href="{{ route('shop.index') }}"
               class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold transition-all whitespace-nowrap
                      {{ !request('category') ? 'bg-slate-900 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                Tout
            </a>
            @foreach($categories as $category)
            <a href="{{ route('shop.index', ['category' => $category->slug] + request()->except('category', 'page')) }}"
               class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold transition-all whitespace-nowrap
                      {{ request('category') === $category->slug ? 'bg-primary-600 text-white shadow-md shadow-primary-600/25' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                {{ $category->name }}
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<div class="container mx-auto px-4 sm:px-6 py-6 md:py-8">

    {{-- ═══════════════════════════════════════════════
         BARRE D'OUTILS — Sort + filtre mobile
    ═══════════════════════════════════════════════ --}}
    <div class="flex items-center justify-between gap-3 mb-5"
         x-data="{ filtersOpen: false }">

        {{-- Bouton filtres mobile --}}
        <button @click="filtersOpen = true"
                class="lg:hidden inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-semibold rounded-xl text-sm shadow-sm transition-all relative">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filtres
            @if($activeFilters > 0)
            <span class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-md">{{ $activeFilters }}</span>
            @endif
        </button>

        {{-- Compteur résultats (desktop) --}}
        <p class="hidden lg:block text-sm text-slate-600 font-medium">
            <span class="font-bold text-slate-900">{{ $products->total() }}</span> produit{{ $products->total() > 1 ? 's' : '' }}
        </p>

        {{-- Sélecteur tri --}}
        <div class="relative ml-auto">
            <select onchange="window.location.href = this.value"
                    class="appearance-none pl-4 pr-10 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 bg-white text-slate-700 text-sm font-medium cursor-pointer shadow-sm">
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Plus récents</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}" {{ request('sort') === 'popular' ? 'selected' : '' }}>Populaires</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
            </div>
        </div>

        {{-- ═══ DRAWER MOBILE FILTRES ═══ --}}
        <div x-show="filtersOpen" x-cloak
             x-transition.opacity
             @click="filtersOpen = false"
             class="lg:hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[80]"></div>

        <aside x-show="filtersOpen" x-cloak
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="lg:hidden fixed top-0 right-0 bottom-0 w-[88vw] max-w-sm bg-white z-[90] flex flex-col shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="font-black text-slate-900 text-lg">Filtres</h2>
                <button @click="filtersOpen = false" class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-5">
                @include('front.shop.partials.filters', ['mobile' => true])
            </div>
        </aside>
    </div>

    {{-- ═══════════════════════════════════════════════
         FILTRES ACTIFS — Chips supprimables
    ═══════════════════════════════════════════════ --}}
    @if($activeFilters > 0)
    <div class="flex flex-wrap items-center gap-2 mb-5 pb-4 border-b border-slate-100">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Filtres actifs :</span>
        @if(request('search'))
        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-medium rounded-full transition-colors">
            Recherche : "{{ request('search') }}"
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        @endif
        @if(request('category'))
        @php $cat = $categories->firstWhere('slug', request('category')); @endphp
        @if($cat)
        <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-medium rounded-full transition-colors">
            {{ $cat->name }}
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        @endif
        @endif
        @if(request('color'))
        @php $col = $colors->firstWhere('slug', request('color')); @endphp
        @if($col)
        <a href="{{ request()->fullUrlWithQuery(['color' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-medium rounded-full transition-colors">
            <span class="w-3 h-3 rounded-full border border-white shadow-sm" style="background:{{ $col->color_code }}"></span>
            {{ $col->value }}
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        @endif
        @endif
        @if(request('min_price') || request('max_price'))
        <a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-medium rounded-full transition-colors">
            Prix : {{ request('min_price', 0) }} - {{ request('max_price', '∞') }} F
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        @endif
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 transition-colors ml-auto">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a2 2 0 012-2h2a2 2 0 012 2v3"/></svg>
            Tout effacer
        </a>
    </div>
    @endif

    <div class="flex gap-8">
        {{-- ═══ SIDEBAR FILTRES DESKTOP ═══ --}}
        <aside class="hidden lg:block w-64 flex-shrink-0">
            @include('front.shop.partials.filters', ['mobile' => false])
        </aside>

        {{-- ═══ GRILLE PRODUITS ═══ --}}
        <div class="flex-1 min-w-0">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                @forelse($products as $product)
                    @include('front.shop.partials.product-card', ['product' => $product])
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-16 md:py-24 px-4">
                        <div class="w-20 h-20 md:w-24 md:h-24 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center mb-5 shadow-inner">
                            <svg class="w-10 h-10 md:w-12 md:h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold text-slate-900 mb-2 text-center">Aucun produit trouvé</h3>
                        <p class="text-slate-500 text-sm mb-6 max-w-sm text-center">Essayez de modifier vos filtres ou parcourez nos catégories.</p>
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-500/25 hover:-translate-y-0.5 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Voir tous les produits
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
                <div class="mt-10 flex justify-center">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
