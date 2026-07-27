@extends('layouts.front')

@php
    $heroBanners        = \App\Models\Banner::getForPosition('hero_slider') ?? collect();
    $productCount       = \App\Models\Product::active()->count();
    $whatsapp           = \App\Models\Setting::get('social_whatsapp');
    $siteName           = \App\Models\Setting::get('site_name', config('app.name'));
    $featuredCategories = $featuredCategories ?? collect();
    $featuredProducts   = $featuredProducts   ?? collect();
    $newProducts        = $newProducts        ?? collect();
    $saleProducts       = $saleProducts       ?? collect();
    $reviews            = $reviews            ?? collect();
    $reviewStats        = $reviewStats        ?? null;
@endphp

@section('title', $siteName)

@section('content')

{{-- ── HERO ────────────────────────────────────────────────────────── --}}
@if($heroBanners->count() > 0)
<section class="relative overflow-hidden bg-slate-900"
    x-data="{
        slide: 0,
        total: {{ $heroBanners->count() }},
        autoplay: null,
        touchX: 0,
        init() { this.autoplay = setInterval(() => this.next(), 5000); },
        next() { this.slide = (this.slide + 1) % this.total; },
        prev() { this.slide = (this.slide - 1 + this.total) % this.total; },
        go(i) { this.slide = i; clearInterval(this.autoplay); this.autoplay = setInterval(() => this.next(), 5000); }
    }"
    @touchstart.passive="touchX = $event.touches[0].clientX"
    @touchend.passive="let dx = $event.changedTouches[0].clientX - touchX; if (dx < -40) next(); else if (dx > 40) prev();">

    <div class="relative min-h-[60vh] md:min-h-[70vh] flex items-center">

        {{-- Backgrounds --}}
        @foreach($heroBanners as $i => $banner)
        <div x-show="slide === {{ $i }}" x-cloak
             x-transition:enter="transition-opacity duration-700"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-500 absolute inset-0"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0">
            @if($banner->image)
            <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}"
                 class="absolute inset-0 w-full h-full object-cover"
                 loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
            @endif
            <div class="absolute inset-0 bg-black/40"></div>
        </div>
        @endforeach

        {{-- Content --}}
        <div class="relative z-10 w-full">
            @foreach($heroBanners as $i => $banner)
            <div x-show="slide === {{ $i }}" x-cloak class="max-w-7xl mx-auto px-4 sm:px-6 py-16 md:py-24">
                <div class="max-w-2xl">
                    @if($banner->title)
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-4 tracking-tight">
                        {!! nl2br(e($banner->title)) !!}
                    </h1>
                    @endif
                    @if($banner->subtitle)
                    <p class="text-white/80 text-base sm:text-lg mb-8 max-w-lg">{{ $banner->subtitle }}</p>
                    @endif
                    <div class="flex flex-wrap gap-3">
                        @if($banner->link && $banner->button_text)
                        <a href="{{ $banner->link }}"
                           class="inline-flex items-center gap-2 px-7 py-3.5 bg-white text-slate-900 font-semibold rounded-lg hover:bg-slate-100 transition-colors text-sm">
                            {{ $banner->button_text }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        @endif
                        @if($whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank"
                           class="inline-flex items-center gap-2 px-7 py-3.5 bg-[#25D366] hover:opacity-90 text-white font-semibold rounded-lg transition-opacity text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Commander sur WhatsApp
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Controls --}}
        @if($heroBanners->count() > 1)
        <button @click="prev()" aria-label="Précédent"
                class="hidden md:flex absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-black/30 hover:bg-black/50 border border-white/20 text-white rounded-full items-center justify-center transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next()" aria-label="Suivant"
                class="hidden md:flex absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-black/30 hover:bg-black/50 border border-white/20 text-white rounded-full items-center justify-center transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
        <div class="absolute bottom-5 left-1/2 -translate-x-1/2 z-20 flex gap-2">
            @foreach($heroBanners as $i => $banner)
            <button @click="go({{ $i }})"
                    :class="slide === {{ $i }} ? 'w-6 bg-white' : 'w-2 bg-white/50'"
                    class="h-1.5 rounded-full transition-all duration-300"
                    aria-label="Slide {{ $i + 1 }}"></button>
            @endforeach
        </div>
        @endif

    </div>
</section>

@else
{{-- Hero statique --}}
<section class="bg-slate-900 min-h-[60vh] md:min-h-[70vh] flex items-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 text-center w-full">
        <p class="text-primary-400 text-xs font-semibold uppercase tracking-widest mb-4">{{ $siteName }}</p>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-5 tracking-tight max-w-3xl mx-auto">
            Découvrez nos meilleurs produits
        </h1>
        <p class="text-slate-400 text-lg mb-8 max-w-xl mx-auto">
            Qualité premium, prix imbattables — livraison partout en Côte d'Ivoire.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('shop.index') }}"
               class="inline-flex items-center gap-2 px-7 py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors text-sm">
                Découvrir la boutique
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            @if($whatsapp)
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank"
               class="inline-flex items-center gap-2 px-7 py-3.5 bg-[#25D366] hover:opacity-90 text-white font-semibold rounded-lg transition-opacity text-sm">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Commander sur WhatsApp
            </a>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ── BANDE CONFIANCE ─────────────────────────────────────────────── --}}
<section class="bg-white border-y border-slate-100">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-slate-100">
            <div class="flex items-center gap-3 px-5 py-5">
                <div class="w-9 h-9 rounded-lg bg-primary-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Livraison rapide</p>
                    <p class="text-xs text-slate-500 hidden sm:block">Abidjan &amp; environs</p>
                </div>
            </div>
            <div class="flex items-center gap-3 px-5 py-5">
                <div class="w-9 h-9 rounded-lg bg-primary-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Paiement sécurisé</p>
                    <p class="text-xs text-slate-500 hidden sm:block">Mobile Money</p>
                </div>
            </div>
            <div class="flex items-center gap-3 px-5 py-5">
                <div class="w-9 h-9 rounded-lg bg-primary-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">WhatsApp 7j/7</p>
                    <p class="text-xs text-slate-500 hidden sm:block">Réponse rapide</p>
                </div>
            </div>
            <div class="flex items-center gap-3 px-5 py-5">
                <div class="w-9 h-9 rounded-lg bg-primary-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Retours faciles</p>
                    <p class="text-xs text-slate-500 hidden sm:block">7 jours garantis</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── CATÉGORIES ──────────────────────────────────────────────────── --}}
@if($featuredCategories->count() > 0)
<section class="py-12 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Nos catégories</h2>
                <p class="text-slate-500 text-sm mt-1">Parcourez notre sélection</p>
            </div>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                Tout voir <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            @foreach($featuredCategories as $cat)
            <a href="{{ route('shop.category', $cat->slug) }}"
               class="group relative aspect-square rounded-xl overflow-hidden hover:-translate-y-0.5 transition-transform duration-200 shadow-sm hover:shadow-md">
                @if($cat->image)
                <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}"
                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                @else
                <div class="absolute inset-0 bg-slate-200 flex items-center justify-center">
                    <span class="text-3xl font-bold text-slate-400">{{ mb_substr($cat->name, 0, 1) }}</span>
                </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-3">
                    <p class="font-semibold text-white text-sm leading-tight">{{ $cat->name }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── PRODUITS VEDETTES ───────────────────────────────────────────── --}}
@if($featuredProducts->count() > 0)
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Sélection du moment</h2>
                <p class="text-slate-500 text-sm mt-1">Nos coups de cœur</p>
            </div>
            <a href="{{ route('shop.index') }}" class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                Voir la boutique <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach($featuredProducts->take(8) as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="sm:hidden flex justify-center mt-6">
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition-colors">
                Voir tous les produits
            </a>
        </div>
    </div>
</section>
@endif

{{-- ── NOUVEAUTÉS ──────────────────────────────────────────────────── --}}
@if($newProducts->count() > 0)
<section class="py-12 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Nouveautés</h2>
                <p class="text-slate-500 text-sm mt-1">Les derniers arrivages</p>
            </div>
            <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                Voir tout <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach($newProducts->take(8) as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="sm:hidden flex justify-center mt-6">
            <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition-colors">
                Voir toutes les nouveautés
            </a>
        </div>
    </div>
</section>
@endif

{{-- ── WHATSAPP CTA ────────────────────────────────────────────────── --}}
@if($whatsapp)
<section class="py-10 bg-[#25D366]">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <svg class="w-10 h-10 text-white mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">Commandez facilement sur WhatsApp</h2>
        <p class="text-white/80 text-base mb-6">Réponse en moins de 30 minutes — 7j/7</p>
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank"
           class="inline-flex items-center gap-2 px-8 py-3.5 bg-white text-[#128C7E] font-bold rounded-lg hover:bg-slate-50 transition-colors text-sm">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
            Écrire sur WhatsApp
        </a>
    </div>
</section>
@endif

{{-- ── AVIS CLIENTS ────────────────────────────────────────────────── --}}
@if($reviews->count() > 0)
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Avis clients</h2>
            @if($reviewStats)
            <div class="flex items-center gap-2 mt-2">
                <div class="flex gap-0.5">
                    @for($s = 1; $s <= 5; $s++)
                    <svg class="w-4 h-4 {{ $s <= round($reviewStats['avg']) ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 7.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <span class="text-sm font-semibold text-slate-700">{{ number_format($reviewStats['avg'], 1) }}</span>
                <span class="text-sm text-slate-500">— {{ $reviewStats['count'] }} avis vérifiés</span>
            </div>
            @endif
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($reviews as $review)
            @php $initial = mb_strtoupper(mb_substr($review->customer?->first_name ?? '?', 0, 1)); @endphp
            <div class="bg-white border border-slate-100 rounded-xl p-5 flex flex-col gap-3">
                <div class="flex gap-0.5">
                    @for($s = 1; $s <= 5; $s++)
                    <svg class="w-4 h-4 {{ $s <= $review->rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 7.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                @if($review->body)
                <p class="text-slate-600 text-sm leading-relaxed">"{{ Str::limit($review->body, 140) }}"</p>
                @endif
                <div class="flex items-center gap-2.5 pt-2 border-t border-slate-100">
                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-sm shrink-0">{{ $initial }}</div>
                    <div>
                        <p class="font-semibold text-slate-900 text-sm">
                            {{ $review->customer?->first_name ?? 'Client' }}
                            {{ mb_strtoupper(mb_substr($review->customer?->last_name ?? '', 0, 1)) }}.
                        </p>
                        <p class="text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── PROMOTIONS ──────────────────────────────────────────────────── --}}
@if($saleProducts->count() > 0)
<section class="py-12 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-end justify-between mb-8">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Promotions en cours</h2>
                <span class="inline-flex items-center px-2.5 py-1 bg-red-100 text-red-600 text-xs font-semibold rounded-full">Durée limitée</span>
            </div>
            <a href="{{ route('shop.index', ['on_sale' => 1]) }}" class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                Voir tout <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach($saleProducts->take(8) as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="sm:hidden flex justify-center mt-6">
            <a href="{{ route('shop.index', ['on_sale' => 1]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition-colors">
                Toutes les promotions
            </a>
        </div>
    </div>
</section>
@endif

@endsection
