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
    🎉 <strong>Livraison gratuite</strong> dès 50 000 F CFA d'achat.&nbsp;
    <a href="{{ route('shop.index') }}" class="underline font-medium">Découvrir →</a>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════════════ --}}
@if($heroBanners->count() > 0)
<section class="relative"
         x-data="{ slide: 0, total: {{ $heroBanners->count() }}, progress: 0 }"
         x-init="setInterval(() => { progress += 0.5; if (progress >= 100) { progress = 0; slide = (slide + 1) % total; } }, 30)">
    <div class="relative overflow-hidden">
        @foreach($heroBanners as $i => $banner)
        <div x-show="slide === {{ $i }}" x-cloak
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 scale-105"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative h-[420px] md:h-[500px] lg:h-[560px]">
            @if($banner->image)
                <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-br from-slate-900 via-primary-950 to-slate-900"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-transparent">
                <div class="container mx-auto px-6 h-full flex items-center">
                    <div class="max-w-xl">
                        @if($banner->title)
                        <h1 class="text-3xl md:text-5xl lg:text-6xl font-black text-white leading-[1.1] mb-4 tracking-tight">{!! nl2br(e($banner->title)) !!}</h1>
                        @endif
                        @if($banner->subtitle)
                        <p class="text-white/70 text-base md:text-lg mb-6 leading-relaxed max-w-md">{{ $banner->subtitle }}</p>
                        @endif
                        @if($banner->link && $banner->button_text)
                        <a href="{{ $banner->link }}" class="inline-flex items-center gap-2 px-8 py-4 bg-primary-600 hover:bg-primary-500 text-white font-bold rounded-2xl transition-all text-sm shadow-xl shadow-primary-600/30 hover:-translate-y-0.5">
                            {{ $banner->button_text }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @if($heroBanners->count() > 1)
    <div class="absolute bottom-0 left-0 right-0 z-20 h-1 bg-white/10">
        <div class="h-full bg-gradient-to-r from-primary-400 to-primary-600 transition-all duration-75 rounded-r-full" :style="'width:' + progress + '%'"></div>
    </div>
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex gap-2">
        @foreach($heroBanners as $i => $banner)
        <button @click="slide = {{ $i }}; progress = 0"
                :class="slide === {{ $i }} ? 'w-8 bg-white' : 'w-2.5 bg-white/40 hover:bg-white/60'"
                class="h-2 rounded-full transition-all duration-300"></button>
        @endforeach
    </div>
    @endif
</section>
@else
{{-- Hero par défaut avec grille produits --}}
<section class="relative overflow-hidden min-h-[520px] lg:min-h-[580px]">
    {{-- Background avec motif --}}
    <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-primary-950"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=60 height=60 viewBox=%270 0 60 60%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cg fill=%27none%27 fill-rule=%27evenodd%27%3E%3Cg fill=%27%23ffffff%27 fill-opacity=%271%27%3E%3Cpath d=%27M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%27/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    {{-- Glow decoratif --}}
    <div class="absolute top-1/2 left-1/4 -translate-y-1/2 w-[500px] h-[500px] bg-primary-600/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-1/4 w-[400px] h-[400px] bg-violet-600/8 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-4 sm:px-6 py-12 md:py-16 lg:py-20 relative z-10">
        <div class="grid lg:grid-cols-2 gap-10 md:gap-12 lg:gap-16 items-center">
            {{-- Texte gauche --}}
            <div class="max-w-xl">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/[0.06] border border-white/[0.08] text-primary-300 rounded-full text-xs font-semibold mb-6 backdrop-blur-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-400"></span>
                    </span>
                    Bienvenue chez {{ $siteName }}
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white leading-[1.05] mb-5 tracking-tight">
                    Découvrez nos
                    <span class="relative">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-amber-400">meilleurs produits</span>
                    </span>
                </h1>
                <p class="text-slate-400 text-base md:text-lg leading-relaxed mb-8 max-w-md">
                    Qualité premium, prix imbattables et livraison express partout en Afrique de l'Ouest.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-2xl hover:-translate-y-0.5 transition-all text-sm shadow-xl shadow-primary-600/25">
                        Explorer la boutique
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    @if($whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-6 py-4 border border-white/15 text-white font-semibold rounded-2xl hover:bg-white/[0.06] backdrop-blur-sm transition-all text-sm">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                    @endif
                </div>
                {{-- Stats --}}
                <div class="flex items-center gap-5 sm:gap-8 mt-8 md:mt-10 pt-6 md:pt-8 border-t border-white/[0.06]">
                    <div>
                        <p class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ number_format($productCount, 0, ',', ' ') }}<span class="text-primary-400">+</span></p>
                        <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Produits</p>
                    </div>
                    <div class="w-px h-9 sm:h-10 bg-white/[0.06]"></div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-black text-white tracking-tight">24<span class="text-primary-400">h</span></p>
                        <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Livraison</p>
                    </div>
                    <div class="w-px h-9 sm:h-10 bg-white/[0.06]"></div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-black text-white tracking-tight">98<span class="text-primary-400">%</span></p>
                        <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Satisfaits</p>
                    </div>
                </div>
            </div>

            {{-- Carousel produits vedettes (auto-rotation) --}}
            @php
                // Priorité aux produits qui ont au moins une image, complétés si besoin par les autres
                $withImages    = $featuredProducts->filter(fn($p) => $p->images->isNotEmpty());
                $withoutImages = $featuredProducts->filter(fn($p) => $p->images->isEmpty());
                $heroProducts  = $withImages->concat($withoutImages)->take(6)->values();
                $heroLabels = ['Coup de cœur', 'Tendance', 'Populaire', 'Meilleure vente', 'Top recommandé', 'Best-seller'];
                $heroBadgeColors = ['bg-amber-500', 'bg-rose-500', 'bg-emerald-500', 'bg-primary-500', 'bg-violet-500', 'bg-cyan-500'];
                $heroSlideGradients = [
                    'from-indigo-600 via-purple-600 to-pink-600',
                    'from-rose-500 via-red-600 to-orange-600',
                    'from-emerald-500 via-teal-600 to-cyan-700',
                    'from-amber-500 via-orange-600 to-rose-600',
                    'from-blue-600 via-indigo-700 to-violet-800',
                    'from-cyan-500 via-blue-600 to-indigo-700',
                ];
            @endphp
            <div class="relative"
                 x-data="{ current: 0, total: {{ $heroProducts->count() }}, paused: false }"
                 x-init="setInterval(() => { if (!paused && total > 1) current = (current + 1) % total }, 4500)"
                 @mouseenter="paused = true" @mouseleave="paused = false">
                <div class="relative h-[300px] sm:h-[360px] md:h-[420px] lg:h-[440px] rounded-2xl md:rounded-3xl overflow-hidden shadow-2xl shadow-black/40 ring-1 ring-white/10">
                    @forelse($heroProducts as $i => $product)
                    @php
                        $img = $product->images->where('is_primary', true)->first() ?? $product->images->first();
                        $imgUrl = $img ? asset('storage/' . $img->path) : null;
                        $bg = $heroSlideGradients[$i] ?? 'from-primary-600 via-primary-700 to-primary-900';
                        $badge = $heroBadgeColors[$i] ?? 'bg-primary-500';
                        $label = $heroLabels[$i] ?? 'Produit phare';
                    @endphp
                    <div x-show="current === {{ $i }}" x-cloak
                         x-transition:enter="transition ease-out duration-700"
                         x-transition:enter-start="opacity-0 scale-105"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-500 absolute inset-0"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute inset-0">
                        <a href="{{ route('shop.product', $product->slug) }}" class="relative block w-full h-full group/card overflow-hidden">
                            {{-- 1. Fond coloré (toujours visible, même sans image) --}}
                            <div class="absolute inset-0 bg-gradient-to-br {{ $bg }}"></div>

                            {{-- 2. Halos décoratifs --}}
                            <div class="absolute inset-0 pointer-events-none"
                                 style="background-image: radial-gradient(at 20% 80%, rgba(255,255,255,0.25) 0%, transparent 50%), radial-gradient(at 80% 15%, rgba(0,0,0,0.35) 0%, transparent 55%);"></div>

                            {{-- 3. Initiale du produit en watermark (toujours visible) --}}
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <span class="text-[140px] sm:text-[180px] md:text-[220px] lg:text-[280px] leading-none font-black text-white/15 select-none drop-shadow-xl">{{ mb_substr($product->name, 0, 1) }}</span>
                            </div>

                            {{-- 4. Image produit (si dispo, supprimée automatiquement si 404) --}}
                            @if($imgUrl)
                                <img src="{{ $imgUrl }}"
                                     alt="{{ $product->name }}"
                                     loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                     onerror="this.remove()"
                                     class="absolute inset-0 w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-700">
                            @endif

                            {{-- 5. Overlay sombre pour lisibilité du texte --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/20 pointer-events-none"></div>

                            {{-- 6. Badge label (toujours visible) --}}
                            <div class="absolute top-5 left-5 z-10">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $badge }} text-white text-xs font-black rounded-xl shadow-2xl tracking-wide uppercase">
                                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                                    {{ $label }}
                                </span>
                            </div>

                            {{-- 7. Prix top-right (toujours visible) --}}
                            <div class="absolute top-5 right-5 z-10 bg-white/95 backdrop-blur-md rounded-2xl px-4 py-2.5 shadow-2xl ring-1 ring-black/5">
                                @if($product->sale_price && $product->sale_price < $product->price)
                                <p class="text-[10px] text-slate-400 line-through font-medium leading-none mb-0.5">{{ format_price($product->price) }}</p>
                                <p class="text-base font-black text-rose-600 leading-none">{{ format_price($product->sale_price) }}</p>
                                @else
                                <p class="text-base font-black text-slate-900 leading-none">{{ format_price($product->sale_price ?? $product->price) }}</p>
                                @endif
                            </div>

                            {{-- 8. Bloc d'info en bas (toujours visible) --}}
                            <div class="absolute bottom-0 inset-x-0 p-4 sm:p-5 md:p-6 z-10">
                                <h3 class="text-white text-lg sm:text-xl md:text-2xl font-black leading-tight mb-1.5 line-clamp-2 drop-shadow-2xl group-hover/card:text-amber-300 transition-colors">
                                    {{ $product->name }}
                                </h3>
                                @if($product->short_description)
                                <p class="text-white/85 text-[11px] sm:text-xs line-clamp-1 mb-3 drop-shadow">{{ $product->short_description }}</p>
                                @endif
                                <span class="inline-flex items-center gap-2 text-white text-xs sm:text-sm font-semibold backdrop-blur-md bg-white/20 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl border border-white/30 group-hover/card:bg-white group-hover/card:text-slate-900 transition-all">
                                    Voir le produit
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 group-hover/card:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </span>
                            </div>
                        </a>
                    </div>
                    @empty
                    <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-primary-700 to-primary-900 flex items-center justify-center">
                        <p class="text-white/60 text-sm">Produits à venir</p>
                    </div>
                    @endforelse
                </div>

                {{-- Indicateurs --}}
                @if($heroProducts->count() > 1)
                <div class="flex items-center justify-center gap-2 mt-5">
                    @foreach($heroProducts as $i => $product)
                    <button type="button" @click="current = {{ $i }}"
                            :class="current === {{ $i }} ? 'w-8 bg-gradient-to-r from-primary-400 to-amber-400' : 'w-2 bg-white/20 hover:bg-white/40'"
                            class="h-2 rounded-full transition-all duration-300"></button>
                    @endforeach
                </div>
                @endif

                {{-- Boutons précédent/suivant (cachés sur mobile pour éviter overflow) --}}
                @if($heroProducts->count() > 1)
                <button type="button" @click="current = (current - 1 + total) % total"
                        class="hidden md:flex absolute top-1/2 -left-4 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white hover:text-slate-900 transition-all items-center justify-center z-10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" @click="current = (current + 1) % total"
                        class="hidden md:flex absolute top-1/2 -right-4 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white hover:text-slate-900 transition-all items-center justify-center z-10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     BARRE DE CONFIANCE - Floating
═══════════════════════════════════════════════ --}}
<section class="relative z-20 -mt-6">
    <div class="container mx-auto px-4 sm:px-6">
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="grid grid-cols-2 md:grid-cols-4 md:divide-x divide-slate-100">
                @php
                    $trustItems = [
                        ['icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0', 'color' => 'blue', 'title' => 'Livraison rapide', 'sub' => '24–48h partout'],
                        ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'emerald', 'title' => 'Paiement sécurisé', 'sub' => 'Mobile Money & CB'],
                        ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'color' => 'amber', 'title' => 'Satisfait ou remboursé', 'sub' => '30 jours de garantie'],
                        ['icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'color' => 'violet', 'title' => 'Support 7j/7', 'sub' => 'Réponse rapide'],
                    ];
                @endphp
                @foreach($trustItems as $item)
                <div class="flex items-center gap-3 py-5 px-5 group hover:bg-slate-50/50 transition-colors first:rounded-l-2xl last:rounded-r-2xl">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-110
                        @if($item['color'] === 'blue') bg-blue-50 text-blue-600
                        @elseif($item['color'] === 'emerald') bg-emerald-50 text-emerald-600
                        @elseif($item['color'] === 'amber') bg-amber-50 text-amber-600
                        @else bg-violet-50 text-violet-600
                        @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">{{ $item['title'] }}</p>
                        <p class="text-[11px] text-slate-400 hidden sm:block">{{ $item['sub'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     CATÉGORIES — Cards modernes avec image
═══════════════════════════════════════════════ --}}
@if($featuredCategories->count() > 0)
<section class="py-14 bg-white">
    <div class="container mx-auto px-6">
        <div class="flex items-end justify-between mb-8">
            <div>
                <span class="text-primary-600 text-xs font-bold uppercase tracking-widest">Explorer</span>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight mt-1">Nos catégories</h2>
            </div>
            <a href="{{ route('shop.index') }}" class="text-sm font-bold text-primary-600 hover:text-primary-700 transition-colors flex items-center gap-1.5 group">
                Tout voir
                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($featuredCategories as $category)
            <a href="{{ route('shop.category', $category->slug) }}"
               class="group relative rounded-2xl overflow-hidden bg-slate-100 aspect-[4/3] flex items-end hover:-translate-y-1 transition-all duration-500 hover:shadow-xl">
                @if($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" loading="lazy">
                @else
                    @php $catIdx = $loop->index % 6; @endphp
                    <div class="absolute inset-0
                        @if($catIdx === 0) bg-gradient-to-br from-indigo-500 to-indigo-700
                        @elseif($catIdx === 1) bg-gradient-to-br from-amber-500 to-orange-600
                        @elseif($catIdx === 2) bg-gradient-to-br from-emerald-500 to-teal-600
                        @elseif($catIdx === 3) bg-gradient-to-br from-rose-500 to-pink-600
                        @elseif($catIdx === 4) bg-gradient-to-br from-blue-500 to-cyan-600
                        @else bg-gradient-to-br from-violet-500 to-purple-600
                        @endif">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-6xl font-black text-white/[0.08]">{{ mb_substr($category->name, 0, 1) }}</span>
                        </div>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                <div class="relative z-10 p-4 w-full">
                    <h3 class="font-bold text-white text-sm leading-tight">{{ $category->name }}</h3>
                    <p class="text-white/60 text-[11px] mt-0.5">{{ $category->products_count ?? 0 }} produits</p>
                </div>
                <div class="absolute top-3 right-3 z-10 w-8 h-8 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 group-hover:rotate-45">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     PRODUITS VEDETTES
═══════════════════════════════════════════════ --}}
@if($featuredProducts->count() > 0)
<section class="py-14 bg-slate-50/80">
    <div class="container mx-auto px-6">
        <div class="flex items-end justify-between mb-8">
            <div>
                <span class="text-primary-600 text-xs font-bold uppercase tracking-widest">Tendances</span>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight mt-1">Produits populaires</h2>
                <p class="text-slate-400 text-sm mt-1">Les plus demandés par nos clients</p>
            </div>
            <a href="{{ route('shop.index') }}" class="text-sm font-bold text-primary-600 hover:text-primary-700 transition-colors flex items-center gap-1.5 group">
                Tout voir
                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            @foreach($featuredProducts as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     BANNIÈRE PROMO
═══════════════════════════════════════════════ --}}
@if($promoBanner)
<section class="py-6 bg-white">
    <div class="container mx-auto px-6">
        <a href="{{ $promoBanner->link ?? '#' }}" class="group relative flex rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-0.5">
            @if($promoBanner->image)
                <img src="{{ asset('storage/' . $promoBanner->image) }}" alt="{{ $promoBanner->title }}" class="w-full h-44 md:h-56 object-cover group-hover:scale-[1.03] transition-transform duration-700" loading="lazy">
            @else
                <div class="w-full h-44 md:h-56 bg-gradient-to-r from-primary-700 via-primary-800 to-violet-800"></div>
            @endif
            @if($promoBanner->title)
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/30 to-transparent flex items-center">
                <div class="p-6 md:p-10">
                    @if($promoBanner->subtitle)
                    <p class="text-primary-300 text-xs font-bold uppercase tracking-widest mb-2">{{ $promoBanner->subtitle }}</p>
                    @endif
                    <h3 class="text-xl md:text-3xl font-black text-white mb-4 tracking-tight">{{ $promoBanner->title }}</h3>
                    @if($promoBanner->button_text)
                    <span class="inline-flex items-center gap-2 px-6 py-3 bg-white text-slate-900 font-bold rounded-xl text-sm group-hover:bg-primary-500 group-hover:text-white transition-all duration-300 shadow-lg">
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
     VENTES FLASH — avec produits et urgence
═══════════════════════════════════════════════ --}}
@if($saleProducts->count() > 0)
<section class="py-14 bg-gradient-to-br from-red-50/50 via-white to-orange-50/30">
    <div class="container mx-auto px-6">
        {{-- Header avec countdown --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8"
             x-data="{
                h: 0, m: 0, s: 0,
                init() {
                    const tick = () => {
                        const now = new Date(), end = new Date(now);
                        end.setHours(23,59,59,999);
                        const d = end - now;
                        this.h = Math.floor(d/3600000);
                        this.m = Math.floor((d%3600000)/60000);
                        this.s = Math.floor((d%60000)/1000);
                    };
                    tick(); setInterval(tick, 1000);
                }
             }">
            <div>
                <span class="text-red-500 text-xs font-bold uppercase tracking-widest">Offres limitées</span>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight mt-1">Ventes Flash</h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs text-slate-500 font-semibold hidden sm:block">Se termine dans</span>
                <div class="flex items-center gap-1.5">
                    <div class="bg-slate-900 text-white text-lg font-black px-3 py-2 rounded-xl min-w-[48px] text-center shadow-lg" x-text="String(h).padStart(2,'0')">00</div>
                    <span class="text-slate-300 font-black text-lg">:</span>
                    <div class="bg-slate-900 text-white text-lg font-black px-3 py-2 rounded-xl min-w-[48px] text-center shadow-lg" x-text="String(m).padStart(2,'0')">00</div>
                    <span class="text-slate-300 font-black text-lg">:</span>
                    <div class="bg-gradient-to-b from-red-500 to-red-600 text-white text-lg font-black px-3 py-2 rounded-xl min-w-[48px] text-center shadow-lg shadow-red-500/30 animate-pulse" x-text="String(s).padStart(2,'0')">00</div>
                </div>
                <a href="{{ route('shop.index', ['sale' => 1]) }}" class="text-sm font-bold text-red-600 hover:text-red-700 flex items-center gap-1 ml-2 group">
                    Tout voir
                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- Produits en promo --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            @foreach($saleProducts as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     NOUVEAUTÉS
═══════════════════════════════════════════════ --}}
@if($newProducts->count() > 0)
<section class="py-14 bg-white">
    <div class="container mx-auto px-6">
        <div class="flex items-end justify-between mb-8">
            <div>
                <span class="text-emerald-600 text-xs font-bold uppercase tracking-widest">Fraîchement ajoutés</span>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight mt-1">Nouveautés</h2>
            </div>
            <a href="{{ route('shop.index', ['sort' => 'newest']) }}" class="text-sm font-bold text-primary-600 hover:text-primary-700 transition-colors flex items-center gap-1.5 group">
                Tout voir
                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            @foreach($newProducts as $product)
                @include('front.shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════
     POURQUOI NOUS CHOISIR
═══════════════════════════════════════════════ --}}
<section class="py-14 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-10">
            <span class="text-primary-600 text-xs font-bold uppercase tracking-widest">Nos engagements</span>
            <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight mt-1">Pourquoi nous choisir ?</h2>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
            @php
                $advantages = [
                    ['icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50', 'title' => 'Qualité garantie', 'desc' => 'Produits vérifiés et sélectionnés avec soin'],
                    ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50', 'title' => 'Meilleurs prix', 'desc' => 'Promotions exclusives chaque semaine'],
                    ['icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50', 'title' => 'Livraison express', 'desc' => '24-48h en Afrique de l\'Ouest'],
                    ['icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'color' => 'text-violet-500', 'bg' => 'bg-violet-50', 'title' => 'SAV réactif', 'desc' => 'WhatsApp & téléphone 7j/7'],
                ];
            @endphp
            @foreach($advantages as $adv)
            <div class="group text-center p-6 rounded-2xl bg-white border border-slate-100 hover:border-slate-200 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                <div class="w-12 h-12 mx-auto rounded-full {{ $adv['bg'] }} flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 {{ $adv['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $adv['icon'] }}"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm mb-1.5">{{ $adv['title'] }}</h3>
                <p class="text-xs text-slate-500 leading-relaxed">{{ $adv['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     TÉMOIGNAGES CLIENTS — Carousel responsive
═══════════════════════════════════════════════ --}}
@php
    $testimonials = [
        ['name' => 'Aminata K.', 'city' => 'Abidjan, Cocody', 'text' => 'Service rapide et produits de qualité ! J\'ai reçu ma commande en moins de 24h. Le SAV est très réactif sur WhatsApp.', 'rating' => 5, 'avatar' => 'A'],
        ['name' => 'Moussa D.', 'city' => 'Yopougon', 'text' => 'Je commande régulièrement et je n\'ai jamais été déçu. Les prix sont vraiment compétitifs et la livraison est ponctuelle.', 'rating' => 5, 'avatar' => 'M'],
        ['name' => 'Fatou B.', 'city' => 'Marcory', 'text' => 'Excellent rapport qualité-prix. L\'équipe est professionnelle et toujours à l\'écoute. Je recommande vivement !', 'rating' => 5, 'avatar' => 'F'],
        ['name' => 'Ibrahim T.', 'city' => 'Treichville', 'text' => 'Site facile à utiliser, paiement Mobile Money sécurisé. Mes commandes arrivent toujours dans les temps.', 'rating' => 5, 'avatar' => 'I'],
    ];
    $avatarGradients = ['from-rose-400 to-pink-600', 'from-blue-400 to-indigo-600', 'from-amber-400 to-orange-600', 'from-emerald-400 to-teal-600'];
@endphp
<section class="py-16 bg-gradient-to-b from-slate-50 via-white to-slate-50/50 relative overflow-hidden">
    {{-- Décorations de fond --}}
    <div class="absolute top-20 left-10 w-72 h-72 bg-primary-200/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-20 right-10 w-72 h-72 bg-amber-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-primary-50 border border-primary-100 rounded-full mb-3">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-600"></span>
                </span>
                <span class="text-primary-700 text-xs font-bold uppercase tracking-widest">+1 000 clients satisfaits</span>
            </div>
            <h2 class="text-2xl md:text-4xl font-black text-slate-900 tracking-tight">
                Ils nous font <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-amber-500">confiance</span>
            </h2>
            <p class="text-slate-500 text-sm md:text-base mt-3 max-w-xl mx-auto">Découvrez ce que nos clients pensent de leur expérience d'achat.</p>
        </div>

        {{-- Note moyenne en évidence --}}
        <div class="flex items-center justify-center gap-3 mb-10">
            <div class="flex items-center gap-0.5">
                @for($s = 0; $s < 5; $s++)
                <svg class="w-6 h-6 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 7.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                @endfor
            </div>
            <span class="text-2xl font-black text-slate-900">4.9</span>
            <span class="text-sm text-slate-500">sur 5 · basé sur 247 avis</span>
        </div>

        {{-- Grille témoignages --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($testimonials as $i => $t)
            <div class="group bg-white rounded-2xl border border-slate-200/60 p-6 hover:border-primary-300 hover:shadow-2xl hover:shadow-primary-500/10 hover:-translate-y-1 transition-all duration-300 relative flex flex-col">
                {{-- Petit indicateur citation discret en haut --}}
                <div class="absolute top-4 right-4 w-7 h-7 rounded-full bg-primary-50 flex items-center justify-center opacity-60 group-hover:opacity-100 group-hover:bg-primary-100 transition-all">
                    <svg class="w-3.5 h-3.5 text-primary-500" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                </div>
                <div class="relative z-10 flex flex-col flex-1">
                    {{-- Étoiles --}}
                    <div class="flex items-center gap-0.5 mb-4">
                        @for($s = 0; $s < $t['rating']; $s++)
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 7.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    {{-- Texte --}}
                    <p class="text-slate-600 text-sm leading-relaxed mb-5 flex-1 pr-6">"{{ $t['text'] }}"</p>
                    {{-- Auteur --}}
                    <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br {{ $avatarGradients[$i] }} flex items-center justify-center text-white font-black text-sm shadow-lg ring-2 ring-white">
                            {{ $t['avatar'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-900 text-sm truncate">{{ $t['name'] }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $t['city'] }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Vérifié
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════
     WHATSAPP CTA
═══════════════════════════════════════════════ --}}
@if($whatsapp)
<section class="py-8 bg-slate-50">
    <div class="container mx-auto px-6">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#075e54] to-[#128c7e] px-8 py-10 md:px-12 md:py-12">
            {{-- Texture subtile --}}
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: url('data:image/svg+xml,%3Csvg width=20 height=20 viewBox=%270 0 20 20%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cg fill=%27%23ffffff%27 fill-opacity=%271%27 fill-rule=%27evenodd%27%3E%3Ccircle cx=%273%27 cy=%273%27 r=%271.5%27/%3E%3Ccircle cx=%2713%27 cy=%2713%27 r=%271.5%27/%3E%3C/g%3E%3C/svg%3E');"></div>
            {{-- Glow --}}
            <div class="absolute -top-20 -right-20 w-60 h-60 bg-green-400/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-emerald-300/10 rounded-full blur-3xl"></div>

            <div class="relative z-10 flex flex-col items-center text-center gap-5">
                <div class="w-16 h-16 bg-white/15 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/10">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <div>
                    <h3 class="font-black text-xl md:text-2xl text-white mb-1">Besoin d'aide ? Écrivez-nous</h3>
                    <p class="text-white/60 text-sm">Réponse rapide · Conseil personnalisé · Disponible 7j/7</p>
                </div>
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}?text={{ urlencode('Bonjour ! Je souhaite des informations sur vos produits.') }}" target="_blank"
                   class="inline-flex items-center gap-2.5 px-8 py-3.5 bg-white text-[#075e54] font-bold rounded-2xl hover:bg-green-50 hover:-translate-y-0.5 transition-all text-sm shadow-xl">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    Écrire sur WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>
@endif

{{-- FOMO Popup --}}
<div x-data="{
        show: false,
        names: ['Aminata', 'Moussa', 'Fatou', 'Ibrahim', 'Awa', 'Ousmane', 'Mariam', 'Boubacar'],
        cities: ['Abidjan', 'Dakar', 'Bamako', 'Ouagadougou', 'Lomé', 'Cotonou'],
        times: ['2 min', '5 min', '8 min', '12 min', '15 min'],
        name: '', city: '', time: '',
        init() {
            const trigger = () => {
                this.name = this.names[Math.floor(Math.random() * this.names.length)];
                this.city = this.cities[Math.floor(Math.random() * this.cities.length)];
                this.time = this.times[Math.floor(Math.random() * this.times.length)];
                this.show = true;
                setTimeout(() => this.show = false, 4000);
            };
            setTimeout(trigger, 10000);
            setInterval(trigger, 30000);
        }
     }"
     x-show="show" x-cloak
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4 scale-95"
     class="fixed bottom-5 left-5 z-50 bg-white rounded-2xl shadow-2xl shadow-slate-300/50 border border-slate-100 p-3.5 max-w-[280px] flex items-center gap-3">
    <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-500/20">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
    </div>
    <div>
        <p class="text-xs font-bold text-slate-900"><span x-text="name"></span> de <span x-text="city"></span></p>
        <p class="text-[11px] text-slate-400">a commandé il y a <span x-text="time" class="text-emerald-600 font-semibold"></span></p>
    </div>
    <button @click="show = false" class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs hover:bg-slate-300 transition-colors">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

@endsection
