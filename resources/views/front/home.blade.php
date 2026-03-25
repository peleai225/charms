@extends('layouts.front')

@section('title', \App\Models\Setting::get('site_name', config('app.name')))

@php
    $heroBanners  = \App\Models\Banner::getForPosition('home_hero');
    $promoBanner  = \App\Models\Banner::active()->position('home_middle')->first();
    $productCount = \App\Models\Product::active()->count();
    $orderCount   = \App\Models\Order::count();
    $whatsapp     = \App\Models\Setting::get('social_whatsapp');
    $siteName     = \App\Models\Setting::get('site_name', config('app.name'));
@endphp

@section('promo_banner')
    🎉 <strong>Livraison gratuite</strong> dès 50 000 F CFA d'achat.&nbsp;
    <a href="{{ route('shop.index') }}" class="underline font-medium">Découvrir →</a>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════════════ --}}
@if($heroBanners->count() > 0)
<section class="relative"
         x-data="{ slide: 0, total: {{ $heroBanners->count() }}, timer: null, progress: 0 }"
         x-init="
            timer = setInterval(() => { slide = (slide + 1) % total; progress = 0 }, 5500);
            setInterval(() => { progress = Math.min(progress + 2, 100) }, 100);
         "
         @mouseenter="clearInterval(timer)"
         @mouseleave="timer = setInterval(() => { slide = (slide + 1) % total; progress = 0 }, 5500)">
    <div class="relative overflow-hidden">
        @foreach($heroBanners as $i => $banner)
        <div x-show="slide === {{ $i }}" x-cloak
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 scale-105"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="relative h-[500px] md:h-[600px] lg:h-[720px]">
            @if($banner->image)
                <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}"
                     class="w-full h-full object-cover" loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
            @else
                <div class="w-full h-full bg-gradient-to-br from-primary-700 via-primary-800 to-slate-900"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/45 to-black/10">
                <div class="container mx-auto px-6 h-full flex items-center">
                    <div class="max-w-2xl">
                        @if($banner->title)
                        <h1 class="text-4xl md:text-5xl lg:text-7xl font-extrabold text-white leading-[1.1] mb-5 drop-shadow-lg">
                            {!! nl2br(e($banner->title)) !!}
                        </h1>
                        @endif
                        @if($banner->subtitle)
                        <p class="text-lg md:text-xl text-white/85 mb-8 leading-relaxed">{{ $banner->subtitle }}</p>
                        @endif
                        @if($banner->link && $banner->button_text)
                        <a href="{{ $banner->link }}"
                           class="inline-flex items-center gap-3 px-8 py-4 bg-primary-600 hover:bg-primary-500 text-white font-bold rounded-2xl shadow-2xl shadow-primary-900/40 hover:-translate-y-1 hover:shadow-primary-600/50 transition-all duration-300 text-lg">
                            {{ $banner->button_text }}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($heroBanners->count() > 1)
    {{-- Arrows --}}
    <button @click="slide = (slide - 1 + total) % total; progress = 0"
            class="absolute left-5 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/15 hover:bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center text-white transition-all border border-white/20">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button @click="slide = (slide + 1) % total; progress = 0"
            class="absolute right-5 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/15 hover:bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center text-white transition-all border border-white/20">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
    {{-- Progress dots --}}
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
        @foreach($heroBanners as $i => $banner)
        <button @click="slide = {{ $i }}; progress = 0"
                class="relative h-2.5 rounded-full transition-all duration-300 overflow-hidden"
                :class="slide === {{ $i }} ? 'w-10 bg-white/30' : 'w-2.5 bg-white/50 hover:bg-white/80'">
            <div x-show="slide === {{ $i }}" class="absolute inset-0 bg-white rounded-full" :style="'width:' + progress + '%'"></div>
        </button>
        @endforeach
    </div>
    @endif
</section>

@else

{{-- Hero par défaut --}}
<section class="relative min-h-[580px] lg:min-h-[720px] bg-gradient-to-br from-slate-950 via-slate-900 to-primary-950 overflow-hidden flex items-center">
    <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-primary-600/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -right-20 w-[600px] h-[600px] bg-violet-600/15 rounded-full blur-3xl"></div>
    <div class="absolute inset-0 opacity-[0.04]"
         style="background-image: linear-gradient(rgba(255,255,255,.8) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.8) 1px, transparent 1px); background-size: 60px 60px;"></div>

    <div class="container mx-auto px-6 py-16 lg:py-24 relative z-10">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div>
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500/15 border border-primary-500/25 text-primary-300 rounded-full text-sm font-semibold mb-7 backdrop-blur-sm">
                    <span class="w-2 h-2 bg-primary-400 rounded-full animate-pulse"></span>
                    Nouvelle collection disponible
                </span>

                <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-white leading-[1.08] mb-7 tracking-tight">
                    Votre boutique
                    <span class="block" style="background: linear-gradient(to right, #7dd3fc, #e879f9, #fcd34d); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        premium en ligne
                    </span>
                </h1>

                <p class="text-slate-200 text-lg leading-relaxed mb-10 max-w-lg">
                    Des produits de qualité sélectionnés avec soin, livrés rapidement partout en Afrique de l'Ouest.
                </p>

                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('shop.index') }}"
                       class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-2xl shadow-2xl shadow-primary-900/50 hover:shadow-primary-600/40 hover:-translate-y-1 transition-all duration-300 text-base">
                        Explorer la boutique
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                    @if($whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}"
                       target="_blank"
                       class="inline-flex items-center gap-3 px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold rounded-2xl backdrop-blur-sm transition-all duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Commander sur WhatsApp
                    </a>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-8 mt-12 pt-8 border-t border-white/10">
                    <div>
                        <p class="text-3xl font-black text-white">{{ number_format($productCount, 0, ',', ' ') }}+</p>
                        <p class="text-sm text-slate-400 mt-0.5">Produits</p>
                    </div>
                    <div class="w-px h-10 bg-white/15"></div>
                    <div>
                        <p class="text-3xl font-black text-white">{{ number_format($orderCount, 0, ',', ' ') }}+</p>
                        <p class="text-sm text-slate-400 mt-0.5">Commandes livrées</p>
                    </div>
                    <div class="w-px h-10 bg-white/15"></div>
                    <div>
                        <p class="text-3xl font-black text-white">98<span class="text-primary-400">%</span></p>
                        <p class="text-sm text-slate-400 mt-0.5">Clients satisfaits</p>
                    </div>
                </div>
            </div>

            <div class="hidden lg:grid grid-cols-2 gap-4 relative">
                <div class="absolute -inset-6 bg-gradient-to-br from-primary-600/10 to-violet-600/10 rounded-3xl blur-2xl"></div>
                @forelse($featuredProducts->take(4) as $i => $product)
                @php $img = $product->images->where('is_primary', true)->first() ?? $product->images->first(); @endphp
                <a href="{{ route('shop.product', $product->slug) }}"
                   class="group relative bg-white/8 border border-white/10 backdrop-blur-sm rounded-2xl overflow-hidden hover:border-primary-400/40 hover:bg-white/12 transition-all duration-300 {{ $i === 0 ? 'row-span-2' : '' }}">
                    @if($img)
                    <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $product->name }}"
                         class="{{ $i === 0 ? 'h-full min-h-[280px]' : 'h-36' }} w-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    @else
                    <div class="{{ $i === 0 ? 'h-full min-h-[280px]' : 'h-36' }} bg-gradient-to-br from-primary-800 to-primary-900 flex items-center justify-center">
                        <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    @endif
                    <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/80 to-transparent">
                        <p class="text-white text-xs font-semibold truncate">{{ $product->name }}</p>
                        <p class="text-primary-300 text-xs font-bold mt-0.5">{{ format_price($product->sale_price) }}</p>
                    </div>
                </a>
                @empty
                @for($i = 0; $i < 4; $i++)
                <div class="bg-gradient-to-br {{ $i === 0 ? 'from-primary-600/20 to-violet-600/20 row-span-2 h-[280px]' : ($i === 1 ? 'from-amber-500/20 to-orange-500/20 h-36' : ($i === 2 ? 'from-pink-500/20 to-rose-500/20 h-36' : 'from-emerald-500/20 to-teal-500/20 h-36')) }} border border-white/10 rounded-2xl flex items-center justify-center">
                    <span class="text-white/30 text-xs font-semibold uppercase tracking-wider">Bientôt</span>
                </div>
                @endfor
                @endforelse
                <div class="absolute -top-4 -right-4 bg-amber-400 text-amber-900 text-xs font-black px-3 py-1.5 rounded-full shadow-lg rotate-3 whitespace-nowrap z-10">
                    Best-sellers
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     BARRE DE CONFIANCE FLOTTANTE
═══════════════════════════════════════════════ --}}
<section class="relative z-10 -mt-8 pb-8">
    <div class="container mx-auto px-6">
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100">
            <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-slate-100">
                <div class="flex items-center gap-4 py-5 px-6 group">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Livraison rapide</p>
                        <p class="text-xs text-slate-500">24-48h partout</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 py-5 px-6 group">
                    <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Paiement sécurisé</p>
                        <p class="text-xs text-slate-500">Mobile Money & CB</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 py-5 px-6 group">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Satisfait ou remboursé</p>
                        <p class="text-xs text-slate-500">30 jours pour changer</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 py-5 px-6 group">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Support 7j/7</p>
                        <p class="text-xs text-slate-500">On vous répond vite</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     CATÉGORIES POPULAIRES
═══════════════════════════════════════════════ --}}
@if($featuredCategories->count() > 0)
<section class="py-20 bg-slate-50">
    <div class="container mx-auto px-6">
        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-primary-600 font-semibold text-sm uppercase tracking-widest mb-2">Catégories</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight">Explorez notre univers</h2>
            </div>
            <a href="{{ route('shop.index') }}" class="hidden md:inline-flex items-center gap-2 text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                Toutes les catégories
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($featuredCategories as $category)
            <a href="{{ route('shop.category', $category->slug) }}"
               class="group relative rounded-2xl overflow-hidden bg-white border border-slate-200 hover:border-primary-300 hover:shadow-xl hover:shadow-primary-100/60 transition-all duration-300 hover:-translate-y-1">
                <div class="aspect-square overflow-hidden">
                    @if($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                    @else
                        @php $catIdx = $loop->index % 6; @endphp
                        <div class="w-full h-full flex items-center justify-center
                            @if($catIdx === 0) bg-gradient-to-br from-primary-500 to-violet-600
                            @elseif($catIdx === 1) bg-gradient-to-br from-amber-500 to-orange-600
                            @elseif($catIdx === 2) bg-gradient-to-br from-emerald-500 to-teal-600
                            @elseif($catIdx === 3) bg-gradient-to-br from-rose-500 to-pink-600
                            @elseif($catIdx === 4) bg-gradient-to-br from-blue-500 to-indigo-600
                            @else bg-gradient-to-br from-cyan-500 to-sky-600
                            @endif">
                            <span class="text-4xl font-black text-white/30">{{ mb_substr($category->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-primary-900/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-4">
                        <span class="text-white text-xs font-bold bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">Découvrir</span>
                    </div>
                </div>
                <div class="p-3 text-center">
                    <h3 class="font-semibold text-slate-800 group-hover:text-primary-600 transition-colors text-sm leading-snug">{{ $category->name }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $category->products_count ?? 0 }} produit{{ ($category->products_count ?? 0) > 1 ? 's' : '' }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     PRODUITS VEDETTES + SOCIAL PROOF
═══════════════════════════════════════════════ --}}
@if($featuredProducts->count() > 0)
<section class="py-20 bg-white">
    <div class="container mx-auto px-6">
        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-primary-600 font-semibold text-sm uppercase tracking-widest mb-2">Sélection</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900">Produits vedettes</h2>
                <p class="text-slate-500 mt-1">Nos produits les plus populaires, choisis par nos clients</p>
            </div>
            <a href="{{ route('shop.index') }}" class="hidden md:inline-flex items-center gap-2 text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                Voir tout
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-6">
            @foreach($featuredProducts as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="text-center mt-10 md:hidden">
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-8 py-3.5 bg-primary-600 text-white font-bold rounded-2xl hover:-translate-y-0.5 transition-all shadow-lg shadow-primary-500/30">
                Voir tous les produits
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     BANNIÈRE URGENCE / PROMO COUNTDOWN
═══════════════════════════════════════════════ --}}
@if($saleProducts->count() > 0)
<section class="py-4 bg-gradient-to-r from-red-600 via-rose-600 to-red-600 relative overflow-hidden"
         x-data="{
            hours: 0, minutes: 0, seconds: 0,
            init() {
                const update = () => {
                    const now = new Date();
                    const midnight = new Date(now);
                    midnight.setHours(23, 59, 59, 999);
                    const diff = midnight - now;
                    this.hours = Math.floor(diff / 3600000);
                    this.minutes = Math.floor((diff % 3600000) / 60000);
                    this.seconds = Math.floor((diff % 60000) / 1000);
                };
                update();
                setInterval(update, 1000);
            }
         }">
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
    <div class="container mx-auto px-6 flex flex-col sm:flex-row items-center justify-center gap-4 relative">
        <div class="flex items-center gap-2 text-white font-bold">
            <svg class="w-5 h-5 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/></svg>
            OFFRES DU JOUR — Expire dans
        </div>
        <div class="flex items-center gap-1.5">
            <span class="bg-white/20 backdrop-blur-sm text-white font-black px-2.5 py-1 rounded-lg text-sm min-w-[36px] text-center" x-text="String(hours).padStart(2,'0')">00</span>
            <span class="text-white font-bold">:</span>
            <span class="bg-white/20 backdrop-blur-sm text-white font-black px-2.5 py-1 rounded-lg text-sm min-w-[36px] text-center" x-text="String(minutes).padStart(2,'0')">00</span>
            <span class="text-white font-bold">:</span>
            <span class="bg-white/20 backdrop-blur-sm text-white font-black px-2.5 py-1 rounded-lg text-sm min-w-[36px] text-center" x-text="String(seconds).padStart(2,'0')">00</span>
        </div>
        <a href="{{ route('shop.index', ['on_sale' => 1]) }}" class="bg-white text-red-600 font-bold text-sm px-5 py-2 rounded-full hover:bg-red-50 transition-colors">
            Voir les offres →
        </a>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     PROMOTIONS
═══════════════════════════════════════════════ --}}
@if($saleProducts->count() > 0)
<section class="py-20 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-red-50 via-rose-50 to-orange-50"></div>
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 via-rose-500 to-orange-500"></div>

    <div class="container mx-auto px-6 relative">
        <div class="flex items-end justify-between mb-12">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-500 text-white text-xs font-black rounded-full mb-3 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                    Stock limité
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900">Promotions en cours</h2>
                <p class="text-slate-500 mt-1">Profitez-en avant qu'il ne soit trop tard</p>
            </div>
            <a href="{{ route('shop.index', ['sale' => 1]) }}" class="hidden md:inline-flex items-center gap-2 text-sm font-semibold text-red-600 hover:text-red-700 transition-colors">
                Tout voir
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-6">
            @foreach($saleProducts as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     BANNIÈRE PROMO (milieu)
═══════════════════════════════════════════════ --}}
@if($promoBanner)
<section class="py-8 bg-white">
    <div class="container mx-auto px-6">
        <a href="{{ $promoBanner->link ?? '#' }}"
           class="group relative flex rounded-3xl overflow-hidden shadow-xl shadow-slate-200/60 hover:shadow-2xl hover:shadow-primary-200/40 transition-all duration-500">
            @if($promoBanner->image)
                <img src="{{ asset('storage/' . $promoBanner->image) }}" alt="{{ $promoBanner->title }}"
                     class="w-full h-56 md:h-72 object-cover group-hover:scale-[1.02] transition-transform duration-700" loading="lazy">
            @else
                <div class="w-full h-56 md:h-72 bg-gradient-to-r from-primary-700 to-violet-700"></div>
            @endif
            @if($promoBanner->title)
            <div class="absolute inset-0 bg-gradient-to-r from-black/65 via-black/30 to-transparent flex items-center">
                <div class="p-8 md:p-12">
                    @if($promoBanner->subtitle)
                    <p class="text-primary-300 text-sm font-semibold uppercase tracking-widest mb-2">{{ $promoBanner->subtitle }}</p>
                    @endif
                    <h3 class="text-2xl md:text-4xl font-extrabold text-white mb-4 leading-tight">{{ $promoBanner->title }}</h3>
                    @if($promoBanner->button_text)
                    <span class="inline-flex items-center gap-2 px-6 py-3 bg-white text-slate-900 font-bold rounded-xl group-hover:bg-primary-500 group-hover:text-white transition-all duration-300">
                        {{ $promoBanner->button_text }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
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
     NOUVEAUTÉS
═══════════════════════════════════════════════ --}}
@if($newProducts->count() > 0)
<section class="py-20 bg-slate-50">
    <div class="container mx-auto px-6">
        <div class="flex items-end justify-between mb-12">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-500 text-white text-xs font-black rounded-full mb-3 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                    Vient d'arriver
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900">Nouveautés</h2>
                <p class="text-slate-500 mt-1">Les derniers produits ajoutés à notre collection</p>
            </div>
            <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="hidden md:inline-flex items-center gap-2 text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                Voir tout
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-6">
            @foreach($newProducts as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     POURQUOI NOUS CHOISIR (Engagement psychologique)
═══════════════════════════════════════════════ --}}
<section class="py-20 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-14">
            <p class="text-primary-600 font-semibold text-sm uppercase tracking-widest mb-2">Pourquoi nous choisir</p>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900">L'expérience {{ $siteName }}</h2>
            <p class="text-slate-500 mt-2 max-w-lg mx-auto">Des milliers de clients nous font confiance chaque jour</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="group p-6 rounded-3xl border border-slate-100 hover:border-transparent hover:shadow-2xl hover:shadow-blue-100/60 hover:-translate-y-2 transition-all duration-300 bg-white text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center mx-auto mb-5 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Livraison express</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Expédition sous 24-48h dans toute l'Afrique de l'Ouest. Suivi en temps réel.</p>
            </div>
            <div class="group p-6 rounded-3xl border border-slate-100 hover:border-transparent hover:shadow-2xl hover:shadow-green-100/60 hover:-translate-y-2 transition-all duration-300 bg-white text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center mx-auto mb-5 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Paiement sécurisé</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Orange Money, Wave, MTN, carte bancaire et paiement à la livraison.</p>
            </div>
            <div class="group p-6 rounded-3xl border border-slate-100 hover:border-transparent hover:shadow-2xl hover:shadow-amber-100/60 hover:-translate-y-2 transition-all duration-300 bg-white text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center mx-auto mb-5 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Meilleurs prix</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Prix compétitifs garantis. Offres exclusives et réductions régulières.</p>
            </div>
            <div class="group p-6 rounded-3xl border border-slate-100 hover:border-transparent hover:shadow-2xl hover:shadow-purple-100/60 hover:-translate-y-2 transition-all duration-300 bg-white text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center mx-auto mb-5 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">SAV réactif</h3>
                <p class="text-sm text-slate-500 leading-relaxed">Support WhatsApp 7j/7. Nous répondons en moins de 30 minutes.</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     TÉMOIGNAGES (Social proof psychologique)
═══════════════════════════════════════════════ --}}
<section class="py-20 bg-slate-50"
         x-data="{ current: 0, total: 4, timer: null }"
         x-init="timer = setInterval(() => current = (current + 1) % total, 6000)">
    <div class="container mx-auto px-6">
        <div class="text-center mb-14">
            <p class="text-primary-600 font-semibold text-sm uppercase tracking-widest mb-2">Témoignages</p>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900">Ce que disent nos clients</h2>
        </div>

        <div class="max-w-3xl mx-auto relative">
            @php
            $testimonials = [
                ['name' => 'Aminata K.', 'city' => 'Abidjan', 'text' => 'Service impeccable ! Ma commande est arrivée le lendemain. Les produits sont exactement comme sur les photos. Je recommande à 100%.', 'stars' => 5],
                ['name' => 'Moussa D.', 'city' => 'Dakar', 'text' => 'Première fois que je commande en ligne et je suis agréablement surpris. Le support WhatsApp m\'a guidé tout le long. Merci !', 'stars' => 5],
                ['name' => 'Fatou S.', 'city' => 'Bamako', 'text' => 'Qualité exceptionnelle et prix très compétitifs. J\'ai comparé partout et c\'est ici que j\'ai trouvé les meilleurs deals.', 'stars' => 5],
                ['name' => 'Ibrahim T.', 'city' => 'Lomé', 'text' => 'Livraison rapide même à Lomé. Le colis était bien emballé et le produit correspond parfaitement à la description. Client fidèle désormais !', 'stars' => 4],
            ];
            @endphp

            @foreach($testimonials as $i => $t)
            <div x-show="current === {{ $i }}"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="bg-white rounded-3xl p-8 md:p-10 shadow-lg border border-slate-100 text-center">
                {{-- Stars --}}
                <div class="flex items-center justify-center gap-1 mb-5">
                    @for($s = 0; $s < $t['stars']; $s++)
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                    @for($s = $t['stars']; $s < 5; $s++)
                    <svg class="w-5 h-5 text-slate-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>

                <svg class="w-10 h-10 text-primary-200 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10H0z"/></svg>

                <p class="text-lg md:text-xl text-slate-700 leading-relaxed mb-6 italic">{{ $t['text'] }}</p>

                <div>
                    <p class="font-bold text-slate-900">{{ $t['name'] }}</p>
                    <p class="text-sm text-slate-500">{{ $t['city'] }}</p>
                </div>
            </div>
            @endforeach

            {{-- Navigation dots --}}
            <div class="flex items-center justify-center gap-2 mt-6">
                @foreach($testimonials as $i => $t)
                <button @click="current = {{ $i }}; clearInterval(timer)"
                        :class="current === {{ $i }} ? 'bg-primary-600 w-8' : 'bg-slate-300 w-2.5 hover:bg-slate-400'"
                        class="h-2.5 rounded-full transition-all duration-300"></button>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     BANNIÈRE WHATSAPP
═══════════════════════════════════════════════ --}}
@if($whatsapp)
<section class="py-14 bg-gradient-to-r from-[#075e54] to-[#128c7e] relative overflow-hidden">
    <div class="absolute inset-0 opacity-10"
         style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
    <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-8 relative">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0">
                <svg class="w-9 h-9 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </div>
            <div class="text-white">
                <h3 class="text-xl font-bold">Commandez directement sur WhatsApp</h3>
                <p class="text-white/80 text-sm mt-0.5">Réponse rapide garantie · Conseil personnalisé · Paiement Mobile Money</p>
            </div>
        </div>
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}?text={{ urlencode('Bonjour ! Je souhaite passer une commande sur ' . $siteName) }}"
           target="_blank"
           class="flex-shrink-0 inline-flex items-center gap-3 px-8 py-4 bg-white text-[#075e54] font-extrabold rounded-2xl hover:bg-green-50 hover:-translate-y-0.5 transition-all shadow-xl text-base">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Commencer sur WhatsApp
        </a>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     NEWSLETTER avec incitation (-10%)
═══════════════════════════════════════════════ --}}
<section class="py-20 relative overflow-hidden bg-slate-950">
    <div class="absolute -top-20 -left-20 w-80 h-80 bg-primary-600/30 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-violet-600/20 rounded-full blur-3xl"></div>
    <div class="absolute inset-0 opacity-[0.03]"
         style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-2xl mx-auto text-center"
             x-data="{ submitted: false }">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500/15 border border-amber-500/25 text-amber-300 rounded-full text-sm font-bold mb-6">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a3 3 0 015-2.236A3 3 0 0114.83 6H16a2 2 0 110 4h-5V9a1 1 0 10-2 0v1H4a2 2 0 110-4h1.17C5.06 5.687 5 5.35 5 5zm4 1V5a1 1 0 10-1 1h1zm2 0a1 1 0 10-1-1v1h1zm-6 7h12v-2H5v2zm12 2a1 1 0 01-1 1H6a1 1 0 01-1-1v-1h12v1z"/></svg>
                -10% sur votre première commande
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
                Rejoignez le club {{ $siteName }}
            </h2>
            <p class="text-slate-400 mb-8 text-lg">
                Recevez vos offres exclusives et un code promo de bienvenue directement dans votre boîte mail.
            </p>

            <form method="POST" action="{{ route('newsletter.subscribe') }}"
                  x-show="!submitted"
                  @submit.prevent="submitted = true; $el.submit()"
                  class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto no-ajax">
                @csrf
                <input type="email" name="email" required
                       placeholder="votre@email.com"
                       class="flex-1 px-5 py-4 rounded-2xl bg-white/10 border border-white/15 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 backdrop-blur-sm text-sm">
                <button type="submit"
                        class="px-7 py-4 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-2xl hover:-translate-y-0.5 hover:shadow-xl hover:shadow-primary-600/30 transition-all text-sm whitespace-nowrap">
                    Obtenir -10%
                </button>
            </form>

            <div x-show="submitted" x-cloak x-transition class="bg-green-500/10 border border-green-500/20 rounded-2xl p-6 max-w-md mx-auto">
                <svg class="w-12 h-12 text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-green-300 font-bold text-lg">Merci !</p>
                <p class="text-green-400/80 text-sm mt-1">Votre code promo arrive dans votre boîte mail.</p>
            </div>

            <p class="text-slate-500 text-xs mt-4">Pas de spam. Désabonnement en un clic.</p>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     MÉTHODES DE PAIEMENT (Trust)
═══════════════════════════════════════════════ --}}
<section class="py-10 bg-white border-t border-slate-100">
    <div class="container mx-auto px-6">
        <div class="flex flex-wrap items-center justify-center gap-8 opacity-60">
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                <svg class="w-8 h-8 text-orange-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                Orange Money
            </div>
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                Wave
            </div>
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                <svg class="w-8 h-8 text-yellow-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                MTN MoMo
            </div>
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Visa / Mastercard
            </div>
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Cash à la livraison
            </div>
        </div>
    </div>
</section>

{{-- État vide --}}
@if($featuredProducts->count() === 0 && $newProducts->count() === 0)
<section class="py-24 bg-slate-50">
    <div class="container mx-auto px-6 text-center">
        <div class="w-24 h-24 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
            <svg class="w-12 h-12 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-900 mb-3">Boutique en cours de création</h2>
        <p class="text-slate-500 max-w-md mx-auto mb-8">Nos produits arrivent très bientôt. Inscrivez-vous à la newsletter pour être le premier informé !</p>
        @auth @if(in_array(auth()->user()->role ?? '', ['admin', 'manager', 'staff']))
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-7 py-3 bg-primary-600 text-white font-semibold rounded-2xl hover:bg-primary-700 transition-colors shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Ajouter des produits
        </a>
        @endif @endauth
    </div>
</section>
@endif

{{-- FOMO: Notification d'achat récent --}}
<div x-data="{
        show: false,
        names: ['Aminata', 'Moussa', 'Fatou', 'Ibrahim', 'Aïcha', 'Kofi', 'Mariam', 'Sékou'],
        cities: ['Abidjan', 'Dakar', 'Bamako', 'Lomé', 'Ouaga', 'Cotonou', 'Niamey'],
        name: '', city: '', minutes: 0,
        init() {
            setTimeout(() => this.showNotif(), 8000);
            setInterval(() => this.showNotif(), 25000);
        },
        showNotif() {
            this.name = this.names[Math.floor(Math.random() * this.names.length)];
            this.city = this.cities[Math.floor(Math.random() * this.cities.length)];
            this.minutes = Math.floor(Math.random() * 12) + 2;
            this.show = true;
            setTimeout(() => this.show = false, 5000);
        }
     }"
     x-show="show"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     x-cloak
     class="fixed bottom-6 left-6 z-40 bg-white rounded-xl shadow-2xl border border-slate-100 p-4 max-w-xs">
    <button @click="show = false" class="absolute top-2 right-2 text-slate-400 hover:text-slate-600">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-800"><span x-text="name">Client</span> de <span x-text="city">Abidjan</span></p>
            <p class="text-xs text-slate-500">a commandé il y a <span x-text="minutes">5</span> min</p>
        </div>
    </div>
</div>

@endsection
