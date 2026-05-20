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
    <span class="font-medium">Livraison gratuite</span> dès 50 000 F CFA d'achat
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════════════════════════
     HERO — Editorial magazine, image full-bleed + typo serif
═══════════════════════════════════════════════════════════════════ --}}
@if($heroBanners->count() > 0)
<section class="relative bg-stone-100 -mt-px"
         x-data="{
            slide: 0,
            total: {{ $heroBanners->count() }},
            paused: false,
            touchStart: 0,
            next() { this.slide = (this.slide + 1) % this.total },
            prev() { this.slide = (this.slide - 1 + this.total) % this.total },
            goTo(i) { this.slide = i }
         }"
         x-init="setInterval(() => { if (!paused && total > 1) next() }, 7000)"
         @mouseenter="paused = true"
         @mouseleave="paused = false"
         @touchstart="touchStart = $event.touches[0].clientX"
         @touchend="
            const dx = $event.changedTouches[0].clientX - touchStart;
            if (dx < -40) next(); else if (dx > 40) prev();
         ">
    <div class="relative overflow-hidden h-[480px] sm:h-[560px] md:h-[640px] lg:h-[720px]">
        @foreach($heroBanners as $i => $banner)
        <div x-show="slide === {{ $i }}" x-cloak
             x-transition:enter="transition-opacity ease-out duration-1000"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-700 absolute inset-0"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0">
            {{-- Fond fallback chaleureux (visible si image absente ou 404) --}}
            <div class="absolute inset-0 bg-gradient-to-br from-amber-800 via-stone-800 to-stone-900"></div>
            {{-- Lettre de marque en watermark, visible si pas d'image --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <span class="font-serif-display text-white/[0.08] text-[280px] sm:text-[400px] md:text-[500px] leading-none -mt-12 select-none">{{ mb_substr($siteName, 0, 1) }}</span>
            </div>
            @if($banner->image)
                <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}"
                     class="relative w-full h-full object-cover"
                     loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                     fetchpriority="{{ $i === 0 ? 'high' : 'auto' }}"
                     onerror="this.remove()">
            @endif
            {{-- Voile très léger pour la lisibilité, pas un overlay brutal --}}
            <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/25 to-transparent md:from-black/50 md:via-black/10"></div>
        </div>
        @endforeach

        {{-- Contenu superposé : positionné en bas-gauche, style éditorial --}}
        <div class="absolute inset-0 flex items-end pb-16 sm:pb-20 md:pb-24 lg:pb-28">
            <div class="container mx-auto px-5 sm:px-8 lg:px-12 max-w-7xl">
                @foreach($heroBanners as $i => $banner)
                <div x-show="slide === {{ $i }}" x-cloak
                     x-transition:enter="transition-all ease-out duration-700 delay-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="max-w-2xl">
                    @if($banner->subtitle)
                    <p class="text-white/90 text-[11px] sm:text-xs font-medium uppercase tracking-[0.3em] mb-4 sm:mb-6">{{ $banner->subtitle }}</p>
                    @endif
                    @if($banner->title)
                    <h1 class="font-serif-display text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-[88px] font-medium text-white leading-[0.98] tracking-tight mb-6 sm:mb-8">
                        {!! nl2br(e($banner->title)) !!}
                    </h1>
                    @endif
                    <div class="flex items-center gap-4 sm:gap-6">
                        @if($banner->link && $banner->button_text)
                        <a href="{{ $banner->link }}" class="inline-flex items-center gap-3 text-white border-b border-white/70 hover:border-white pb-1 text-sm sm:text-base font-medium tracking-wide transition-all">
                            {{ $banner->button_text }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        @else
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-3 text-white border-b border-white/70 hover:border-white pb-1 text-sm sm:text-base font-medium tracking-wide transition-all">
                            Voir la collection
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Compteur en haut à droite, discret --}}
        @if($heroBanners->count() > 1)
        <div class="absolute top-6 right-5 sm:top-8 sm:right-8 z-10">
            <span class="text-white/90 text-xs sm:text-sm font-medium tracking-wider tabular-nums" x-text="String(slide + 1).padStart(2, '0') + ' / ' + String(total).padStart(2, '0')"></span>
        </div>

        {{-- Indicateurs verticaux à droite (desktop) --}}
        <div class="hidden lg:flex absolute right-8 top-1/2 -translate-y-1/2 z-10 flex-col gap-3">
            @foreach($heroBanners as $i => $banner)
            <button @click="goTo({{ $i }})" aria-label="Slide {{ $i + 1 }}"
                    :class="slide === {{ $i }} ? 'bg-white h-10' : 'bg-white/40 hover:bg-white/60 h-5'"
                    class="w-px transition-all duration-500"></button>
            @endforeach
        </div>

        {{-- Indicateurs en bas (mobile) --}}
        <div class="lg:hidden absolute bottom-6 left-1/2 -translate-x-1/2 z-10 flex gap-2">
            @foreach($heroBanners as $i => $banner)
            <button @click="goTo({{ $i }})" aria-label="Slide {{ $i + 1 }}"
                    :class="slide === {{ $i }} ? 'w-8 bg-white' : 'w-4 bg-white/40'"
                    class="h-px transition-all duration-500"></button>
            @endforeach
        </div>
        @endif
    </div>
</section>
@else
{{-- Hero éditorial par défaut — chaleur + asymétrie --}}
<section class="relative bg-gradient-to-br from-stone-50 via-amber-50/40 to-stone-100 overflow-hidden">
    {{-- Décorations subtiles --}}
    <div class="absolute -top-32 -right-32 w-[500px] h-[500px] bg-amber-100/40 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 w-[500px] h-[500px] bg-stone-200/40 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-5 sm:px-8 lg:px-12 max-w-7xl py-16 sm:py-20 md:py-28 lg:py-32 relative">
        <div class="grid lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            <div class="lg:col-span-7 min-w-0">
                <div class="inline-flex items-center gap-2 px-3 py-1 mb-6 bg-white/70 backdrop-blur-sm rounded-full border border-amber-200/50">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                    <span class="text-amber-800 text-[11px] font-medium uppercase tracking-[0.2em]">Édition {{ date('Y') }} · {{ $productCount }} produits</span>
                </div>
                <h1 class="font-serif-display text-5xl sm:text-6xl md:text-7xl lg:text-[80px] xl:text-[96px] font-medium text-stone-900 leading-[0.98] tracking-tight mb-6 sm:mb-8">
                    Des objets<br>choisis,<br>
                    <em class="not-italic text-amber-700">une vie meilleure.</em>
                </h1>
                <p class="text-stone-700 text-base sm:text-lg md:text-xl leading-relaxed max-w-md mb-8 md:mb-10">
                    La sélection {{ $siteName }} — qualité, prix justes, livraison rapide partout en Côte d'Ivoire.
                </p>
                <div class="flex flex-wrap items-center gap-5 sm:gap-7">
                    <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-3 px-7 py-3.5 bg-stone-900 hover:bg-amber-800 text-white text-sm font-medium rounded-full transition-colors">
                        Explorer la boutique
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    @if($whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" class="text-stone-700 text-sm font-medium border-b border-amber-700/40 hover:border-stone-900 pb-0.5 transition-colors">
                        Nous écrire
                    </a>
                    @endif
                </div>
            </div>
            <div class="lg:col-span-5 hidden lg:block">
                <div class="relative">
                    {{-- Carte produit avec décoration --}}
                    <div class="relative aspect-[4/5] rounded-lg overflow-hidden shadow-2xl shadow-amber-900/10">
                        @php $heroProduct = $featuredProducts->first(); @endphp
                        @if($heroProduct && $heroProduct->images->isNotEmpty())
                            <img src="{{ asset('storage/' . $heroProduct->images->first()->path) }}"
                                 alt="{{ $heroProduct->name }}"
                                 class="absolute inset-0 w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                            <div class="absolute bottom-0 inset-x-0 p-6">
                                <p class="text-white/70 text-xs uppercase tracking-widest mb-1">Coup de cœur</p>
                                <p class="font-serif-display text-white text-2xl font-medium leading-tight">{{ $heroProduct->name }}</p>
                            </div>
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-700 via-orange-800 to-stone-900 flex items-end p-6">
                                <p class="font-serif-display text-white/20 text-[200px] absolute -top-12 -right-4 leading-none">{{ mb_substr($siteName, 0, 1) }}</p>
                                <p class="relative font-serif-display text-white text-2xl font-medium leading-tight">{{ $siteName }}</p>
                            </div>
                        @endif
                    </div>
                    {{-- Badge décoratif --}}
                    <div class="absolute -top-4 -left-4 w-20 h-20 rounded-full bg-amber-100 border-4 border-white flex items-center justify-center text-amber-900 text-center shadow-lg rotate-[-6deg]">
                        <div class="text-[10px] leading-tight font-medium uppercase tracking-wider">
                            Made<br>in CI<br>★
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     01 / ENGAGEMENTS — Magazine TOC style
═══════════════════════════════════════════════════════════════════ --}}
<section class="bg-white border-t border-stone-200">
    <div class="container mx-auto px-5 sm:px-8 lg:px-12 max-w-7xl py-12 md:py-16">
        @php
            $values = [
                ['n' => '01', 't' => 'Livraison', 'd' => '24 à 48 heures partout en Côte d\'Ivoire.'],
                ['n' => '02', 't' => 'Paiement', 'd' => 'Mobile Money, cartes, ou à la livraison.'],
                ['n' => '03', 't' => 'Retours', 'd' => 'Sous 30 jours, sans question.'],
                ['n' => '04', 't' => 'Conseil', 'd' => 'Notre équipe vous répond en moins d\'une heure.'],
            ];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4">
            @foreach($values as $v)
            <div class="py-4 md:py-0 md:px-6 lg:px-8 {{ !$loop->first ? 'md:border-l border-stone-200' : '' }}">
                <span class="block text-stone-400 text-xs font-medium tracking-widest mb-2">{{ $v['n'] }}</span>
                <h3 class="font-serif-display text-xl md:text-2xl font-medium text-stone-900 mb-1.5">{{ $v['t'] }}</h3>
                <p class="text-stone-500 text-xs md:text-sm leading-relaxed">{{ $v['d'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     02 / CATÉGORIES — Magazine layout, asymétrique
═══════════════════════════════════════════════════════════════════ --}}
@if($featuredCategories->count() > 0)
<section class="bg-stone-50 py-16 md:py-24">
    <div class="container mx-auto px-5 sm:px-8 lg:px-12 max-w-7xl">
        <div class="flex items-end justify-between mb-10 md:mb-14">
            <div>
                <p class="text-stone-500 text-xs font-medium uppercase tracking-[0.3em] mb-3">— Catégories</p>
                <h2 class="font-serif-display text-4xl sm:text-5xl md:text-6xl font-medium text-stone-900 tracking-tight leading-none">
                    Explorez<br><em class="not-italic text-stone-400">nos univers</em>
                </h2>
            </div>
            <a href="{{ route('shop.index') }}" class="hidden md:inline-flex items-center gap-2 text-sm font-medium text-stone-700 border-b border-stone-300 hover:border-stone-700 pb-0.5 transition-colors">
                Toutes les catégories
            </a>
        </div>

        @php
            $cats = $featuredCategories->take(6);
            // Palette warm pour les catégories sans image
            $fallbackColors = [
                ['from' => 'from-amber-700', 'via' => 'via-orange-700', 'to' => 'to-red-800'],
                ['from' => 'from-stone-700', 'via' => 'via-stone-800', 'to' => 'to-stone-900'],
                ['from' => 'from-emerald-700', 'via' => 'via-teal-800', 'to' => 'to-slate-900'],
                ['from' => 'from-indigo-700', 'via' => 'via-blue-800', 'to' => 'to-slate-900'],
                ['from' => 'from-rose-700', 'via' => 'via-pink-800', 'to' => 'to-purple-900'],
                ['from' => 'from-amber-800', 'via' => 'via-yellow-900', 'to' => 'to-stone-900'],
            ];
        @endphp
        @if($cats->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 md:gap-5">
            @foreach($cats as $i => $category)
            @php
                $isHero = $i === 0;
                $colClass = 'col-span-1 md:col-span-1 aspect-[4/5]';
                if ($isHero) $colClass = 'col-span-2 md:col-span-3 md:row-span-2 aspect-square md:aspect-auto';
                if ($i === 1) $colClass = 'col-span-2 md:col-span-3 aspect-[16/9] md:aspect-[3/2]';
                $palette = $fallbackColors[$i % 6];
            @endphp
            <a href="{{ route('shop.category', $category->slug) }}"
               class="group relative overflow-hidden rounded-sm {{ $colClass }}">
                @if($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-1000 ease-out" loading="lazy">
                @else
                    {{-- Fallback warm avec lettre stylée --}}
                    <div class="absolute inset-0 bg-gradient-to-br {{ $palette['from'] }} {{ $palette['via'] }} {{ $palette['to'] }}"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="font-serif-display text-white/20 text-[200px] md:text-[280px] {{ $isHero ? 'md:text-[360px]' : '' }} font-medium leading-none -mt-8">{{ mb_substr($category->name, 0, 1) }}</span>
                    </div>
                    {{-- Texture grain subtile --}}
                    <div class="absolute inset-0 opacity-[0.03] pointer-events-none mix-blend-overlay"
                         style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cfilter id='n'%3E%3CfeTurbulence baseFrequency='.9'/%3E%3C/filter%3E%3Crect width='100' height='100' filter='url(%23n)'/%3E%3C/svg%3E&quot;)"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t {{ $isHero ? 'from-black/70' : 'from-black/60' }} via-black/10 to-transparent"></div>
                <div class="absolute bottom-0 inset-x-0 p-4 md:p-6">
                    <p class="text-white/70 text-[10px] md:text-xs font-medium tracking-widest uppercase mb-1">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }} · {{ $category->products_count ?? 0 }} pcs</p>
                    <h3 class="font-serif-display text-white text-xl md:text-2xl {{ $isHero ? 'lg:text-3xl' : '' }} font-medium leading-none">{{ $category->name }}</h3>
                </div>
                {{-- Petit indicateur en haut à droite --}}
                <div class="absolute top-4 right-4 md:top-5 md:right-5 opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-white text-stone-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </span>
                </div>
            </a>
            @endforeach
        </div>
        @endif

        <div class="md:hidden flex justify-center mt-8">
            <a href="{{ route('shop.index') }}" class="text-sm font-medium text-stone-700 border-b border-stone-300 pb-0.5">
                Toutes les catégories →
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     03 / SÉLECTION — Produits populaires
═══════════════════════════════════════════════════════════════════ --}}
@if($featuredProducts->count() > 0)
<section class="bg-white py-16 md:py-24">
    <div class="container mx-auto px-5 sm:px-8 lg:px-12 max-w-7xl">
        <div class="flex items-end justify-between mb-10 md:mb-14">
            <div>
                <p class="text-stone-500 text-xs font-medium uppercase tracking-[0.3em] mb-3">— Sélection</p>
                <h2 class="font-serif-display text-4xl sm:text-5xl md:text-6xl font-medium text-stone-900 tracking-tight leading-none">
                    Coups de<br><em class="not-italic text-stone-400">cœur</em>
                </h2>
            </div>
            <a href="{{ route('shop.index') }}" class="hidden md:inline-flex items-center gap-2 text-sm font-medium text-stone-700 border-b border-stone-300 hover:border-stone-700 pb-0.5 transition-colors">
                Voir la boutique
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5 md:gap-7">
            @foreach($featuredProducts->take(8) as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="md:hidden flex justify-center mt-8">
            <a href="{{ route('shop.index') }}" class="text-sm font-medium text-stone-700 border-b border-stone-300 pb-0.5">
                Voir tous les produits →
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     04 / MANIFESTE — Split éditorial avec couleur d'accent
═══════════════════════════════════════════════════════════════════ --}}
<section class="bg-amber-50 py-16 md:py-24 lg:py-28 overflow-hidden relative">
    {{-- Subtile texture de fond --}}
    <div class="absolute top-0 right-0 w-1/2 h-full opacity-30 pointer-events-none"
         style="background-image: radial-gradient(circle at 30% 50%, rgba(180, 83, 9, 0.08) 0%, transparent 70%);"></div>

    <div class="container mx-auto px-5 sm:px-8 lg:px-12 max-w-7xl relative">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">

            {{-- COLONNE GAUCHE : Manifeste --}}
            <div class="min-w-0">
                <p class="text-amber-700 text-xs font-medium uppercase tracking-[0.3em] mb-4">— Notre approche</p>
                <h2 class="font-serif-display text-4xl sm:text-5xl md:text-6xl font-medium text-stone-900 tracking-tight leading-[1.02] mb-6 md:mb-8 break-words">
                    Chaque produit,<br>une décision.
                </h2>
                <p class="text-stone-700 text-base md:text-lg leading-relaxed mb-5">
                    Nous ne vendons pas tout. Nous choisissons. Chaque article est testé, évalué, approuvé — pour qu'il mérite sa place dans votre panier.
                </p>
                <p class="text-stone-600 text-sm md:text-base leading-relaxed mb-8 md:mb-10">
                    Quand vous achetez chez nous, vous achetez le résultat de ce travail.
                </p>
                <a href="{{ route('about') }}" class="inline-flex items-center gap-3 text-sm font-medium text-stone-900 border-b border-amber-700/50 hover:border-stone-900 pb-1 transition-colors">
                    En savoir plus sur nous
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            {{-- COLONNE DROITE : Témoignages — taille raisonnable + min-w-0 --}}
            <div class="min-w-0 relative">
                <div class="bg-white/60 backdrop-blur-sm border border-amber-200/50 rounded-lg p-8 md:p-10 lg:p-12 shadow-sm"
                     x-data="{ t: 0, items: [
                        { name: 'Aminata K.', city: 'Cocody, Abidjan', text: 'Service rapide, produits conformes à la description. Mon premier achat ne sera pas le dernier.' },
                        { name: 'Moussa D.', city: 'Yopougon', text: 'Je commande pour ma boutique. Toujours dans les délais, qualité constante.' },
                        { name: 'Fatou B.', city: 'Marcory', text: 'L équipe répond en quelques minutes sur WhatsApp. Un vrai professionnalisme.' }
                     ] }"
                     x-init="setInterval(() => t = (t + 1) % items.length, 7000)">

                    <svg class="w-10 h-10 text-amber-600/40 mb-6" fill="currentColor" viewBox="0 0 32 32">
                        <path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z"/>
                    </svg>

                    <div class="relative min-h-[150px] sm:min-h-[180px]">
                        <template x-for="(item, i) in items" :key="i">
                            <blockquote x-show="t === i" x-cloak
                                        x-transition:enter="transition-opacity ease-out duration-700 delay-200"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition-opacity ease-in duration-300 absolute inset-0">
                                <p class="font-serif-display text-xl sm:text-2xl md:text-3xl font-medium text-stone-900 leading-[1.3] tracking-tight mb-6" x-text="'« ' + item.text + ' »'"></p>
                                <footer class="flex items-center gap-3 text-sm pt-4 border-t border-amber-200/40">
                                    <p class="font-semibold text-stone-900" x-text="item.name"></p>
                                    <span class="text-amber-700/50">·</span>
                                    <p class="text-stone-500" x-text="item.city"></p>
                                </footer>
                            </blockquote>
                        </template>
                    </div>

                    <div class="flex items-center gap-2 mt-8">
                        <template x-for="(item, i) in items" :key="i">
                            <button @click="t = i" :aria-label="'Témoignage ' + (i + 1)"
                                    :class="t === i ? 'bg-amber-700 w-8' : 'bg-amber-700/30 hover:bg-amber-700/50 w-2'"
                                    class="h-px transition-all duration-500"></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     05 / EN PROMOTION (si dispo)
═══════════════════════════════════════════════════════════════════ --}}
@if($saleProducts->count() > 0)
<section class="bg-white py-16 md:py-24">
    <div class="container mx-auto px-5 sm:px-8 lg:px-12 max-w-7xl">
        <div class="flex items-end justify-between mb-10 md:mb-14">
            <div>
                <p class="text-stone-500 text-xs font-medium uppercase tracking-[0.3em] mb-3">— Promotions</p>
                <h2 class="font-serif-display text-4xl sm:text-5xl md:text-6xl font-medium text-stone-900 tracking-tight leading-none">
                    En <em class="not-italic text-stone-400">soldes</em>
                </h2>
            </div>
            <a href="{{ route('shop.index', ['sale' => 1]) }}" class="hidden md:inline-flex items-center gap-2 text-sm font-medium text-stone-700 border-b border-stone-300 hover:border-stone-700 pb-0.5 transition-colors">
                Toutes les offres
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5 md:gap-7">
            @foreach($saleProducts->take(8) as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="md:hidden flex justify-center mt-8">
            <a href="{{ route('shop.index', ['sale' => 1]) }}" class="text-sm font-medium text-stone-700 border-b border-stone-300 pb-0.5">
                Toutes les offres →
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     06 / NOUVEAUTÉS
═══════════════════════════════════════════════════════════════════ --}}
@if($newProducts->count() > 0)
<section class="bg-stone-50 py-16 md:py-24">
    <div class="container mx-auto px-5 sm:px-8 lg:px-12 max-w-7xl">
        <div class="flex items-end justify-between mb-10 md:mb-14">
            <div>
                <p class="text-stone-500 text-xs font-medium uppercase tracking-[0.3em] mb-3">— Arrivages</p>
                <h2 class="font-serif-display text-4xl sm:text-5xl md:text-6xl font-medium text-stone-900 tracking-tight leading-none">
                    Tout <em class="not-italic text-stone-400">nouveau</em>
                </h2>
            </div>
            <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="hidden md:inline-flex items-center gap-2 text-sm font-medium text-stone-700 border-b border-stone-300 hover:border-stone-700 pb-0.5 transition-colors">
                Voir les nouveautés
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5 md:gap-7">
            @foreach($newProducts->take(8) as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="md:hidden flex justify-center mt-8">
            <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="text-sm font-medium text-stone-700 border-b border-stone-300 pb-0.5">
                Voir les nouveautés →
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     07 / BANNIÈRE PROMO ÉDITORIALE
═══════════════════════════════════════════════════════════════════ --}}
@if($promoBanner)
<section class="bg-stone-900 py-16 md:py-24 overflow-hidden">
    <a href="{{ $promoBanner->link ?? route('shop.index') }}" class="group block">
        <div class="container mx-auto px-5 sm:px-8 lg:px-12 max-w-7xl">
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                <div class="order-2 lg:order-1">
                    @if($promoBanner->subtitle)
                    <p class="text-stone-400 text-xs font-medium uppercase tracking-[0.3em] mb-4">— {{ $promoBanner->subtitle }}</p>
                    @endif
                    @if($promoBanner->title)
                    <h3 class="font-serif-display text-4xl sm:text-5xl md:text-6xl font-medium text-white leading-[1.02] tracking-tight mb-6 md:mb-8">{{ $promoBanner->title }}</h3>
                    @endif
                    <span class="inline-flex items-center gap-3 text-white border-b border-white/40 group-hover:border-white pb-1 text-sm font-medium tracking-wide transition-colors">
                        {{ $promoBanner->button_text ?? 'Découvrir' }}
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </span>
                </div>
                <div class="order-1 lg:order-2 relative aspect-[4/3] sm:aspect-[3/2] lg:aspect-square rounded overflow-hidden">
                    @if($promoBanner->image)
                        <img src="{{ asset('storage/' . $promoBanner->image) }}" alt="{{ $promoBanner->title }}"
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-1000 ease-out" loading="lazy">
                    @else
                        <div class="absolute inset-0 bg-stone-700"></div>
                    @endif
                </div>
            </div>
        </div>
    </a>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════════
     08 / CONTACT WHATSAPP — Sobre, naturel
═══════════════════════════════════════════════════════════════════ --}}
@if($whatsapp)
<section class="bg-white py-16 md:py-24 border-t border-stone-200">
    <div class="container mx-auto px-5 sm:px-8 lg:px-12 max-w-3xl text-center">
        <p class="text-stone-500 text-xs font-medium uppercase tracking-[0.3em] mb-4">— Une question ?</p>
        <h2 class="font-serif-display text-4xl sm:text-5xl md:text-6xl font-medium text-stone-900 leading-tight tracking-tight mb-5 md:mb-7">
            Parlons-en.
        </h2>
        <p class="text-stone-600 text-base md:text-lg leading-relaxed mb-8 md:mb-10 max-w-lg mx-auto">
            Une équipe humaine, accessible et réactive. Écrivez-nous sur WhatsApp, nous répondons en quelques minutes.
        </p>
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}?text={{ urlencode('Bonjour, je souhaite des informations.') }}" target="_blank"
           class="inline-flex items-center gap-3 px-7 py-3.5 bg-stone-900 hover:bg-stone-800 text-white text-sm font-medium rounded-full transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
            Écrire sur WhatsApp
        </a>
    </div>
</section>
@endif

@endsection
