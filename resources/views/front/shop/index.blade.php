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

    $sortLabels = [
        'newest' => 'Plus récents',
        'price_asc' => 'Prix croissant',
        'price_desc' => 'Prix décroissant',
        'popular' => 'Populaires',
    ];
    $currentSort = request('sort', 'newest');
@endphp

{{-- ═══════════════════════════════════════════════
     HERO BOUTIQUE — Éditorial sobre + couleurs settings
═══════════════════════════════════════════════ --}}
<section class="relative bg-stone-900 text-white overflow-hidden">
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-primary-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-accent-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-16 lg:py-20 relative">
        {{-- Fil d'Ariane --}}
        <nav class="text-xs sm:text-sm text-stone-400 mb-5 sm:mb-7 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Accueil
            </a>
            <span class="text-stone-600">/</span>
            <span class="text-white">Boutique</span>
        </nav>

        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 md:gap-8">
            <div class="flex-1 min-w-0">
                <p class="text-stone-400 text-[10px] sm:text-xs font-medium uppercase tracking-[0.25em] mb-3">— Notre catalogue</p>
                <h1 class="font-serif-display text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-medium text-white leading-[1.02] tracking-tight">
                    La <em class="not-italic text-accent-400">boutique.</em>
                </h1>
                <p class="text-stone-300 text-sm sm:text-base mt-3 sm:mt-4">
                    <span class="font-medium text-white">{{ $products->total() }}</span> produit{{ $products->total() > 1 ? 's' : '' }} sélectionné{{ $products->total() > 1 ? 's' : '' }} pour vous
                </p>
            </div>

            {{-- Trust signals desktop --}}
            <div class="hidden md:flex items-center gap-5 lg:gap-8 text-xs lg:text-sm text-stone-300">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-white/10 backdrop-blur-md border border-white/15 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1"/></svg>
                    </div>
                    <span>Livraison 24-48h</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-white/10 backdrop-blur-md border border-white/15 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <span>Retours 30 jours</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     CHIPS CATÉGORIES — Sticky horizontal scroll
═══════════════════════════════════════════════ --}}
@if($categories->count() > 0)
<section class="bg-white border-b border-stone-100 sticky top-0 z-30 backdrop-blur-md bg-white/95">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-3.5">
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-none -mx-1 px-1">
            <a href="{{ route('shop.index') }}"
               class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-medium transition-all whitespace-nowrap
                      {{ !request('category') ? 'bg-stone-900 text-white' : 'bg-stone-50 text-stone-600 hover:bg-stone-100' }}">
                Tout voir
            </a>
            @foreach($categories as $category)
            <a href="{{ route('shop.index', ['category' => $category->slug] + request()->except('category', 'page')) }}"
               class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-medium transition-all whitespace-nowrap
                      {{ request('category') === $category->slug ? 'bg-primary-600 text-white shadow-sm shadow-primary-600/25' : 'bg-stone-50 text-stone-600 hover:bg-stone-100' }}">
                {{ $category->name }}
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10">

    {{-- ═══════════════════════════════════════════════
         BARRE D'OUTILS — Sort + filtre mobile
    ═══════════════════════════════════════════════ --}}
    <div class="flex items-center justify-between gap-3 mb-6 md:mb-8"
         x-data="{ filtersOpen: false }">

        {{-- Bouton filtres mobile --}}
        <button @click="filtersOpen = true"
                class="lg:hidden inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-stone-200 hover:border-stone-300 text-stone-700 font-medium rounded-full text-sm shadow-sm transition-all relative">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filtres
            @if($activeFilters > 0)
            <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 bg-primary-600 text-white text-[10px] font-bold rounded-full">{{ $activeFilters }}</span>
            @endif
        </button>

        {{-- Compteur desktop --}}
        <p class="hidden lg:flex items-center gap-2 text-sm text-stone-600">
            <span class="font-semibold text-stone-900 tabular-nums">{{ $products->total() }}</span>
            <span>résultat{{ $products->total() > 1 ? 's' : '' }}</span>
        </p>

        {{-- Tri (dropdown stylé) --}}
        <div class="relative ml-auto" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-stone-200 hover:border-stone-300 text-stone-700 font-medium rounded-full text-sm shadow-sm transition-all">
                <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                <span class="hidden sm:inline">Trier :</span>
                <span class="text-stone-900">{{ $sortLabels[$currentSort] ?? 'Plus récents' }}</span>
                <svg class="w-3.5 h-3.5 text-stone-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="absolute right-0 mt-2 w-52 bg-white border border-stone-200 rounded-xl shadow-xl py-2 z-50">
                @foreach($sortLabels as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['sort' => $key]) }}"
                   class="flex items-center justify-between px-4 py-2.5 text-sm hover:bg-stone-50 transition-colors {{ $currentSort === $key ? 'text-primary-600 font-semibold bg-primary-50/40' : 'text-stone-700' }}">
                    {{ $label }}
                    @if($currentSort === $key)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </a>
                @endforeach
            </div>
        </div>

        {{-- DRAWER MOBILE FILTRES --}}
        <div x-show="filtersOpen" x-cloak
             x-transition.opacity
             @click="filtersOpen = false"
             class="lg:hidden fixed inset-0 bg-stone-900/60 backdrop-blur-sm z-[80]"></div>

        <aside x-show="filtersOpen" x-cloak
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="lg:hidden fixed top-0 right-0 bottom-0 w-[88vw] max-w-sm bg-white z-[90] flex flex-col shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                <h2 class="font-serif-display font-medium text-stone-900 text-2xl">Filtres</h2>
                <button @click="filtersOpen = false" class="w-9 h-9 rounded-full hover:bg-stone-100 flex items-center justify-center transition-colors" aria-label="Fermer">
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
    <div class="flex flex-wrap items-center gap-2 mb-6 pb-5 border-b border-stone-100">
        <span class="text-[10px] font-medium text-stone-500 uppercase tracking-[0.2em]">— Filtres actifs</span>
        @if(request('search'))
        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-medium rounded-full transition-colors">
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
        <a href="{{ request()->fullUrlWithQuery(['color' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-medium rounded-full transition-colors">
            <span class="w-3 h-3 rounded-full border border-white shadow-sm" style="background:{{ $col->color_code }}"></span>
            {{ $col->value }}
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        @endif
        @endif
        @if(request('min_price') || request('max_price'))
        <a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-medium rounded-full transition-colors">
            Prix : {{ request('min_price', 0) }} – {{ request('max_price', '∞') }} F
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        @endif
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-stone-500 hover:text-stone-900 border-b border-stone-300 hover:border-stone-900 ml-auto pb-0.5 transition-colors rounded-none">
            Tout effacer
        </a>
    </div>
    @endif

    <div class="flex gap-8 lg:gap-12">
        {{-- SIDEBAR FILTRES DESKTOP --}}
        <aside class="hidden lg:block w-64 flex-shrink-0">
            @include('front.shop.partials.filters', ['mobile' => false])
        </aside>

        {{-- GRILLE PRODUITS --}}
        <div class="flex-1 min-w-0">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-5 md:gap-6">
                @forelse($products as $product)
                    @include('front.shop.partials.product-card', ['product' => $product])
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-16 md:py-28 px-4">
                        <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-stone-100 flex items-center justify-center mb-5">
                            <svg class="w-10 h-10 md:w-12 md:h-12 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <h3 class="font-serif-display text-2xl md:text-3xl font-medium text-stone-900 mb-2 text-center">
                            Aucun résultat
                        </h3>
                        <p class="text-stone-500 text-sm md:text-base mb-7 max-w-sm text-center leading-relaxed">
                            Aucun produit ne correspond à vos critères. Essayez d'élargir votre recherche.
                        </p>
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-stone-900 hover:bg-stone-800 text-white font-medium rounded-full transition-colors text-sm">
                            Voir tous les produits
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
                <div class="mt-12 md:mt-16">
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
