@extends('layouts.front')

@section('title', \App\Models\Setting::get('site_name', config('app.name')))

@php
    $heroBanners  = \App\Models\Banner::getForPosition('home_hero');
    $promoBanner  = \App\Models\Banner::active()->position('home_middle')->first();
    $productCount = \App\Models\Product::active()->count();
    $whatsapp     = \App\Models\Setting::get('social_whatsapp');
    $siteName     = \App\Models\Setting::get('site_name', config('app.name'));
@endphp

@section('promo_banner')
    <strong class="font-bold">Livraison gratuite</strong> dès 50 000 F CFA d'achat.&nbsp;
    <a href="{{ route('shop.index') }}" class="underline font-medium">Découvrir</a>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════
     HERO — Éditorial, image + texte, pas de gradients texte
═══════════════════════════════════════════════ --}}
@if($heroBanners->count() > 0)
<section class="relative bg-slate-100"
         x-data="{
            slide: 0,
            total: {{ $heroBanners->count() }},
            paused: false,
            touchStart: 0,
            next() { this.slide = (this.slide + 1) % this.total; },
            prev() { this.slide = (this.slide - 1 + this.total) % this.total; },
            goTo(i) { this.slide = i; }
         }"
         x-init="setInterval(() => { if (!paused && total > 1) next() }, 6000)"
         @mouseenter="paused = true"
         @mouseleave="paused = false"
         @touchstart="touchStart = $event.touches[0].clientX"
         @touchend="
            const dx = $event.changedTouches[0].clientX - touchStart;
            if (dx < -40) next();
            else if (dx > 40) prev();
         ">
    <div class="relative overflow-hidden h-[420px] sm:h-[500px] md:h-[560px] lg:h-[620px]">
        @foreach($heroBanners as $i => $banner)
        <div x-show="slide === {{ $i }}" x-cloak
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-500 absolute inset-0"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0">
            @if($banner->image)
                <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}"
                     class="w-full h-full object-cover"
                     loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                     fetchpriority="{{ $i === 0 ? 'high' : 'auto' }}">
            @else
                <div class="w-full h-full bg-slate-800"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-black/10 md:via-black/30 md:to-transparent"></div>
            <div class="absolute inset-0 flex items-center">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="max-w-lg">
                        @if($banner->subtitle)
                        <p class="text-white/80 text-xs sm:text-sm font-medium uppercase tracking-[0.2em] mb-3 sm:mb-4">{{ $banner->subtitle }}</p>
                        @endif
                        @if($banner->title)
                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-[1.1] mb-5 sm:mb-7 tracking-tight" style="font-family: 'Poppins', sans-serif;">{!! nl2br(e($banner->title)) !!}</h1>
                        @endif
                        @if($banner->link && $banner->button_text)
                        <a href="{{ $banner->link }}"
                           class="group inline-flex items-center gap-3 px-6 sm:px-8 py-3 sm:py-3.5 bg-white hover:bg-slate-100 text-slate-900 font-semibold rounded-full text-sm sm:text-base transition-all">
                            {{ $banner->button_text }}
                            <span class="inline-flex items-center justify-center w-7 h-7 bg-slate-900 text-white rounded-full group-hover:translate-x-1 transition-transform">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </span>
                        </a>
                        @else
                        <a href="{{ route('shop.index') }}"
                           class="group inline-flex items-center gap-3 px-6 sm:px-8 py-3 sm:py-3.5 bg-white hover:bg-slate-100 text-slate-900 font-semibold rounded-full text-sm sm:text-base transition-all">
                            Voir la collection
                            <span class="inline-flex items-center justify-center w-7 h-7 bg-slate-900 text-white rounded-full group-hover:translate-x-1 transition-transform">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($heroBanners->count() > 1)
    {{-- Navigation simple en bas, sans glass-morphism --}}
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex items-center gap-3">
        <button @click="prev()" aria-label="Précédent"
                class="hidden sm:flex items-center justify-center w-10 h-10 rounded-full bg-white/15 hover:bg-white hover:text-slate-900 text-white border border-white/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <div class="flex gap-2">
            @foreach($heroBanners as $i => $banner)
            <button @click="goTo({{ $i }})" aria-label="Slide {{ $i + 1 }}"
                    :class="slide === {{ $i }} ? 'w-8 bg-white' : 'w-2 bg-white/50 hover:bg-white/70'"
                    class="h-1 rounded-full transition-all duration-500"></button>
            @endforeach
        </div>
        <button @click="next()" aria-label="Suivant"
                class="hidden sm:flex items-center justify-center w-10 h-10 rounded-full bg-white/15 hover:bg-white hover:text-slate-900 text-white border border-white/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
    @endif
</section>
@else
{{-- Hero par défaut si aucune bannière --}}
<section class="relative bg-slate-900 overflow-hidden">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 md:py-28">
        <div class="max-w-3xl">
            <p class="text-amber-400 text-xs font-medium uppercase tracking-[0.2em] mb-4">Bienvenue chez {{ $siteName }}</p>
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-[1.05] mb-6 tracking-tight" style="font-family: 'Poppins', sans-serif;">
                La sélection<br>qui change tout.
            </h1>
            <p class="text-slate-300 text-base sm:text-lg leading-relaxed mb-8 max-w-xl">
                Qualité premium, prix justes, livraison express en Afrique de l'Ouest.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('shop.index') }}"
                   class="group inline-flex items-center gap-3 px-7 py-3.5 bg-white text-slate-900 font-semibold rounded-full text-sm transition-all hover:bg-slate-100">
                    Explorer la boutique
                    <span class="inline-flex items-center justify-center w-7 h-7 bg-slate-900 text-white rounded-full group-hover:translate-x-1 transition-transform">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </span>
                </a>
                @if($whatsapp)
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-6 py-3.5 border border-white/20 text-white font-medium rounded-full text-sm hover:bg-white/5 transition-all">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></svg>
                    Nous contacter
                </a>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     BANDE TRUST — Très simple, monochrome
═══════════════════════════════════════════════ --}}
<section class="bg-white border-b border-slate-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
            @php
                $trust = [
                    ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'title' => 'Livraison 24-48h', 'desc' => 'Partout en Côte d\'Ivoire'],
                    ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'title' => 'Paiement sécurisé', 'desc' => 'Mobile Money & cartes'],
                    ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'title' => 'Retours 30 jours', 'desc' => 'Satisfait ou remboursé'],
                    ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'title' => 'Support 7j/7', 'desc' => 'WhatsApp & téléphone'],
                ];
            @endphp
            @foreach($trust as $t)
            <div class="flex items-start gap-3 md:gap-4">
                <div class="shrink-0 w-10 h-10 md:w-11 md:h-11 rounded-lg bg-slate-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-slate-900 text-sm md:text-base leading-tight">{{ $t['title'] }}</p>
                    <p class="text-slate-500 text-xs md:text-sm mt-0.5">{{ $t['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     CATÉGORIES — Style éditorial
═══════════════════════════════════════════════ --}}
@if($featuredCategories->count() > 0)
<section class="py-12 md:py-20 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8 md:mb-12">
            <div>
                <p class="text-slate-500 text-xs font-medium uppercase tracking-[0.2em] mb-2">Catégories</p>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-slate-900 tracking-tight" style="font-family: 'Poppins', sans-serif;">
                    Explorez par univers
                </h2>
            </div>
            <a href="{{ route('shop.index') }}" class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                Voir tout
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            @foreach($featuredCategories->take(8) as $i => $category)
            <a href="{{ route('shop.category', $category->slug) }}"
               class="group relative overflow-hidden rounded-lg aspect-[3/4] bg-slate-100">
                @if($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy">
                @else
                    @php $palette = ['bg-slate-700','bg-slate-800','bg-stone-700','bg-zinc-700','bg-neutral-700','bg-stone-800']; @endphp
                    <div class="absolute inset-0 {{ $palette[$i % 6] }}"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 inset-x-0 p-4 md:p-5">
                    <p class="text-white/70 text-[10px] md:text-xs font-medium uppercase tracking-widest mb-1">{{ $category->products_count ?? 0 }} produit{{ ($category->products_count ?? 0) > 1 ? 's' : '' }}</p>
                    <h3 class="text-white font-semibold text-base sm:text-lg md:text-xl leading-tight">{{ $category->name }}</h3>
                </div>
            </a>
            @endforeach
        </div>

        <div class="sm:hidden flex justify-center mt-6">
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                Voir toutes les catégories
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     PRODUITS POPULAIRES
═══════════════════════════════════════════════ --}}
@if($featuredProducts->count() > 0)
<section class="py-12 md:py-20 bg-slate-50/50 border-y border-slate-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8 md:mb-12">
            <div>
                <p class="text-slate-500 text-xs font-medium uppercase tracking-[0.2em] mb-2">Sélection</p>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-slate-900 tracking-tight" style="font-family: 'Poppins', sans-serif;">
                    Les plus appréciés
                </h2>
            </div>
            <a href="{{ route('shop.index') }}" class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                Voir tout
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            @foreach($featuredProducts->take(8) as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="sm:hidden flex justify-center mt-6">
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-full">
                Voir tous les produits
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     BANNIÈRE PROMO — Sobre, éditoriale
═══════════════════════════════════════════════ --}}
@if($promoBanner)
<section class="py-8 md:py-12 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ $promoBanner->link ?? '#' }}" class="group relative block rounded-lg md:rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-shadow duration-500">
            @if($promoBanner->image)
                <img src="{{ asset('storage/' . $promoBanner->image) }}" alt="{{ $promoBanner->title }}"
                     class="w-full h-48 sm:h-56 md:h-72 object-cover group-hover:scale-[1.02] transition-transform duration-700" loading="lazy">
            @else
                <div class="w-full h-48 sm:h-56 md:h-72 bg-slate-800"></div>
            @endif
            @if($promoBanner->title)
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/30 to-transparent flex items-center">
                <div class="p-6 md:p-12 max-w-lg">
                    @if($promoBanner->subtitle)
                    <p class="text-white/80 text-xs font-medium uppercase tracking-[0.2em] mb-3">{{ $promoBanner->subtitle }}</p>
                    @endif
                    <h3 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-4 sm:mb-5 tracking-tight leading-tight" style="font-family: 'Poppins', sans-serif;">{{ $promoBanner->title }}</h3>
                    @if($promoBanner->button_text)
                    <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-slate-900 font-semibold rounded-full text-sm">
                        {{ $promoBanner->button_text }}
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </span>
                    @endif
                </div>
            </div>
            @endif
        </a>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     PROMOTIONS — Sans countdown théâtral
═══════════════════════════════════════════════ --}}
@if($saleProducts->count() > 0)
<section class="py-12 md:py-20 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8 md:mb-12">
            <div>
                <p class="text-rose-600 text-xs font-medium uppercase tracking-[0.2em] mb-2">Promotions</p>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-slate-900 tracking-tight" style="font-family: 'Poppins', sans-serif;">
                    En soldes
                </h2>
            </div>
            <a href="{{ route('shop.index', ['sale' => 1]) }}" class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                Voir tout
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            @foreach($saleProducts->take(8) as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="sm:hidden flex justify-center mt-6">
            <a href="{{ route('shop.index', ['sale' => 1]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-full">
                Voir toutes les promotions
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     NOUVEAUTÉS
═══════════════════════════════════════════════ --}}
@if($newProducts->count() > 0)
<section class="py-12 md:py-20 bg-slate-50/50 border-y border-slate-100">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8 md:mb-12">
            <div>
                <p class="text-slate-500 text-xs font-medium uppercase tracking-[0.2em] mb-2">Arrivages</p>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-slate-900 tracking-tight" style="font-family: 'Poppins', sans-serif;">
                    Nouveautés
                </h2>
            </div>
            <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                Voir tout
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            @foreach($newProducts->take(8) as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="sm:hidden flex justify-center mt-6">
            <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-full">
                Voir toutes les nouveautés
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     SPLIT EDITORIAL — Pourquoi nous + Témoignage
═══════════════════════════════════════════════ --}}
<section class="py-12 md:py-20 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            {{-- Texte engagement --}}
            <div>
                <p class="text-slate-500 text-xs font-medium uppercase tracking-[0.2em] mb-3">À propos</p>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-slate-900 tracking-tight mb-5 sm:mb-7 leading-tight" style="font-family: 'Poppins', sans-serif;">
                    Une boutique pensée pour vous.
                </h2>
                <p class="text-slate-600 text-base md:text-lg leading-relaxed mb-8 max-w-lg">
                    Nous sélectionnons chaque produit avec rigueur. Notre engagement&nbsp;: la qualité, des prix justes, et un service client à la hauteur de votre confiance.
                </p>

                <div class="space-y-5 max-w-md">
                    @php
                        $engagements = [
                            ['t' => 'Produits sélectionnés', 'd' => 'Chaque article testé et approuvé avant mise en ligne.'],
                            ['t' => 'Service client humain', 'd' => 'Une vraie personne vous répond en moins d\'une heure.'],
                            ['t' => 'Paiement en confiance', 'd' => 'Mobile Money, cartes, ou paiement à la livraison.'],
                        ];
                    @endphp
                    @foreach($engagements as $e)
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900 mb-1">{{ $e['t'] }}</p>
                            <p class="text-slate-500 text-sm leading-relaxed">{{ $e['d'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-8 sm:mt-10">
                    <a href="{{ route('shop.index') }}" class="group inline-flex items-center gap-3 px-7 py-3.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-full text-sm transition-all">
                        Découvrir nos produits
                        <span class="inline-flex items-center justify-center w-6 h-6 bg-white text-slate-900 rounded-full group-hover:translate-x-1 transition-transform">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </span>
                    </a>
                </div>
            </div>

            {{-- Témoignage éditorial --}}
            <div class="relative"
                 x-data="{ t: 0, items: [
                    { name: 'Aminata K.', city: 'Cocody', text: 'Service rapide et produits de qualité. J\'ai reçu ma commande en moins de 24h.' },
                    { name: 'Moussa D.', city: 'Yopougon', text: 'Je commande régulièrement. Prix compétitifs, livraison toujours ponctuelle.' },
                    { name: 'Fatou B.', city: 'Marcory', text: 'Excellent rapport qualité-prix. L\'équipe est à l\'écoute et professionnelle.' }
                 ] }"
                 x-init="setInterval(() => t = (t + 1) % items.length, 6000)">
                <div class="bg-slate-50 rounded-xl p-8 md:p-12 lg:p-14">
                    <svg class="w-10 h-10 text-slate-300 mb-6" fill="currentColor" viewBox="0 0 32 32"><path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z"/></svg>

                <div class="relative min-h-[180px] md:min-h-[160px]">
                    <template x-for="(item, i) in items" :key="i">
                        <div x-show="t === i" x-cloak
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100">
                            <p class="text-slate-800 text-lg md:text-xl leading-relaxed mb-6" x-text="'« ' + item.text + ' »'"></p>
                            <div class="flex items-center gap-3">
                                <p class="font-semibold text-slate-900" x-text="item.name"></p>
                                <span class="text-slate-300">·</span>
                                <p class="text-slate-500 text-sm" x-text="item.city"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center gap-2 mt-8 pt-6 border-t border-slate-200">
                    <template x-for="(item, i) in items" :key="i">
                        <button @click="t = i" :class="t === i ? 'bg-slate-900 w-8' : 'bg-slate-300 w-2'"
                                class="h-1 rounded-full transition-all"></button>
                    </template>
                </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     CTA WHATSAPP — Sobre, fonctionnel
═══════════════════════════════════════════════ --}}
@if($whatsapp)
<section class="bg-slate-900 py-12 md:py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center">
            <p class="text-emerald-400 text-xs font-medium uppercase tracking-[0.2em] mb-3">Contact direct</p>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-4 tracking-tight" style="font-family: 'Poppins', sans-serif;">
                Une question ? Écrivez-nous.
            </h2>
            <p class="text-slate-400 text-base mb-7 max-w-md mx-auto">
                Conseil personnalisé, suivi de commande, ou simple renseignement&nbsp;: notre équipe est disponible 7j/7.
            </p>
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}?text={{ urlencode('Bonjour ! Je souhaite des informations sur vos produits.') }}" target="_blank"
               class="inline-flex items-center gap-3 px-7 py-3.5 bg-emerald-500 hover:bg-emerald-400 text-white font-semibold rounded-full text-sm transition-all">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                Écrire sur WhatsApp
            </a>
        </div>
    </div>
</section>
@endif

@endsection
