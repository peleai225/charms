@extends('layouts.front')
@section('title', (isset($currentCategory) && $currentCategory) ? $currentCategory->name . ' — Boutique' : 'Boutique')
@section('meta_description', 'Découvrez notre sélection de produits. Livraison rapide en Côte d\'Ivoire.')

@section('content')
@php
    $activeFilters = collect([
        'search' => request('search'), 'category' => request('category'),
        'color'  => request('color'),  'min_price' => request('min_price'),
        'max_price' => request('max_price'), 'on_sale' => request('on_sale'),
        'in_stock' => request('in_stock'),
    ])->filter()->count();
    $sortOptions = ['latest'=>'Plus récents','price_asc'=>'Prix croissant','price_desc'=>'Prix décroissant','popular'=>'Meilleures ventes','on_sale'=>'Promotions'];
    $currentSort = request('sort', 'latest');
    $xClose = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>';
    $xCheck = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>';
@endphp

<div class="bg-white border-b border-slate-100 py-6">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-sm mb-3 text-slate-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-slate-700 transition-colors">Accueil</a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('shop.index') }}" class="hover:text-slate-700 transition-colors">Boutique</a>
            @if(isset($currentCategory) && $currentCategory)
                <span class="text-slate-300">/</span><span class="text-slate-700">{{ $currentCategory->name }}</span>
            @endif
        </nav>
        <div class="flex items-baseline gap-3">
            <h1 class="text-2xl font-bold text-slate-900">{{ (isset($currentCategory) && $currentCategory) ? $currentCategory->name : 'Boutique' }}</h1>
            <span class="text-sm text-slate-500">{{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }}</span>
        </div>
    </div>
</div>

@if($categories->count() > 0)
<div class="bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center gap-2 overflow-x-auto scrollbar-none">
        <a href="{{ route('shop.index') }}" class="shrink-0 px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-colors {{ !request('category') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Tout voir</a>
        @foreach($categories as $cat)
        <a href="{{ route('shop.index', array_merge(request()->except('category','page'), ['category'=>$cat->slug])) }}"
           class="shrink-0 px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-colors {{ request('category')===$cat->slug ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $cat->name }}</a>
        @endforeach
    </div>
</div>
@endif

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-10" x-data="{ filtersOpen: false, viewMode: 'grid' }">

    {{-- Barre outils --}}
    <div class="flex items-center gap-3 mb-5">
        <button @click="filtersOpen = true" class="lg:hidden inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-medium rounded-lg text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filtres @if($activeFilters > 0)<span class="w-5 h-5 bg-primary-600 text-white text-[10px] font-bold rounded-full inline-flex items-center justify-center">{{ $activeFilters }}</span>@endif
        </button>
        <p class="hidden lg:block text-sm text-slate-500"><span class="font-semibold text-slate-900">{{ $products->total() }}</span> résultat{{ $products->total() > 1 ? 's' : '' }}</p>
        <div class="ml-auto flex items-center gap-2">
            <div class="hidden sm:flex border border-slate-200 rounded-lg overflow-hidden">
                <button @click="viewMode='grid'" :class="viewMode==='grid'?'bg-slate-900 text-white':'bg-white text-slate-400 hover:bg-slate-50'" class="p-2 transition-colors" aria-label="Grille"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></button>
                <button @click="viewMode='list'" :class="viewMode==='list'?'bg-slate-900 text-white':'bg-white text-slate-400 hover:bg-slate-50'" class="p-2 transition-colors" aria-label="Liste"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            </div>
            <div class="relative" x-data="{open:false}">
                <button @click="open=!open" @click.outside="open=false" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-medium rounded-lg text-sm transition-colors">
                    <span class="hidden sm:inline text-slate-400 text-xs">Trier :</span>
                    <span>{{ $sortOptions[$currentSort] ?? 'Plus récents' }}</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="open&&'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-50">
                    @foreach($sortOptions as $key => $label)
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>$key,'page'=>null]) }}" class="flex items-center justify-between px-4 py-2.5 text-sm transition-colors {{ $currentSort===$key ? 'text-primary-600 font-semibold bg-primary-50' : 'text-slate-700 hover:bg-slate-50' }}">
                        {{ $label }}
                        @if($currentSort===$key)<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $xCheck !!}</svg>@endif
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres actifs --}}
    @if($activeFilters > 0)
    <div class="flex flex-wrap items-center gap-2 mb-5 pb-5 border-b border-slate-100">
        <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Actifs :</span>
        @if(request('search'))<a href="{{ request()->fullUrlWithQuery(['search'=>null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-md">"{{ request('search') }}" <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $xClose !!}</svg></a>@endif
        @if(request('category'))@php $ac=$categories->firstWhere('slug',request('category')); @endphp
            @if($ac)<a href="{{ request()->fullUrlWithQuery(['category'=>null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-medium rounded-md">{{ $ac->name }} <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $xClose !!}</svg></a>@endif
        @endif
        @if(request('color'))@php $col=isset($colors)?$colors->firstWhere('id',request('color')):null; @endphp
            @if($col)<a href="{{ request()->fullUrlWithQuery(['color'=>null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-md"><span class="w-3 h-3 rounded-full border border-slate-200" style="background-color:{{ $col->value }}"></span>{{ $col->label }} <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $xClose !!}</svg></a>@endif
        @endif
        @if(request('min_price')||request('max_price'))<a href="{{ request()->fullUrlWithQuery(['min_price'=>null,'max_price'=>null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-md">Prix {{ number_format(request('min_price',0)) }}–{{ request('max_price')?number_format(request('max_price')):'∞' }} F <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $xClose !!}</svg></a>@endif
        @if(request('on_sale'))<a href="{{ request()->fullUrlWithQuery(['on_sale'=>null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-medium rounded-md">Promo <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $xClose !!}</svg></a>@endif
        @if(request('in_stock'))<a href="{{ request()->fullUrlWithQuery(['in_stock'=>null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-medium rounded-md">En stock <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $xClose !!}</svg></a>@endif
        <a href="{{ route('shop.index') }}" class="ml-auto text-xs text-slate-400 hover:text-slate-700 underline underline-offset-2 transition-colors">Tout effacer</a>
    </div>
    @endif

    <div class="flex gap-10 lg:gap-12">

        {{-- Sidebar desktop --}}
        <aside class="hidden lg:block w-60 flex-shrink-0">
            <div class="sticky top-20 space-y-7">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Filtres</span>
                    @if($activeFilters > 0)<a href="{{ route('shop.index') }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium">Effacer ({{ $activeFilters }})</a>@endif
                </div>

                @if($categories->count() > 0)
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5">Catégories</p>
                    <ul class="space-y-0.5">
                        <li><a href="{{ route('shop.index') }}" class="block py-1.5 text-sm transition-colors {{ !request('category') ? 'text-primary-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">Tout voir</a></li>
                        @foreach($categories as $cat)
                        <li x-data="{open:{{ (request('category')===$cat->slug||$cat->children->where('slug',request('category'))->count()>0)?'true':'false' }}}">
                            <div class="flex items-center justify-between">
                                <a href="{{ route('shop.index', array_merge(request()->except('category','page'), ['category'=>$cat->slug])) }}" class="flex-1 py-1.5 text-sm transition-colors {{ request('category')===$cat->slug ? 'text-primary-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">{{ $cat->name }}</a>
                                @if($cat->children->count() > 0)<button @click="open=!open" class="p-1 text-slate-400 hover:text-slate-700 transition-colors"><svg class="w-3.5 h-3.5 transition-transform" :class="open&&'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>@endif
                            </div>
                            @if($cat->children->count() > 0)
                            <ul x-show="open" x-cloak class="ml-3 border-l border-slate-100 pl-3 mt-0.5 space-y-0.5">
                                @foreach($cat->children as $child)<li><a href="{{ route('shop.index', array_merge(request()->except('category','page'),['category'=>$child->slug])) }}" class="block py-1 text-sm transition-colors {{ request('category')===$child->slug ? 'text-primary-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">{{ $child->name }}</a></li>@endforeach
                            </ul>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5">Prix (XOF)</p>
                    <form method="GET" action="{{ route('shop.index') }}" class="space-y-3">
                        @foreach(request()->except('min_price','max_price','page') as $k=>$v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="{{ $priceRange->min ?? '0' }}" min="0" class="flex-1 px-2.5 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-primary-500">
                            <span class="text-slate-300 text-sm">–</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="{{ $priceRange->max ?? '∞' }}" min="0" class="flex-1 px-2.5 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-primary-500">
                        </div>
                        <button type="submit" class="w-full py-2 text-xs font-medium bg-slate-900 hover:bg-slate-700 text-white rounded-lg transition-colors">Appliquer</button>
                    </form>
                </div>

                @if(isset($colors) && $colors->count() > 0)
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2.5">Couleur</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($colors as $color)
                        <a href="{{ request()->fullUrlWithQuery(['color'=>request('color')==$color->id?null:$color->id,'page'=>null]) }}" title="{{ $color->label }}" class="w-7 h-7 rounded-full border-2 transition-all {{ request('color')==$color->id ? 'border-primary-600 ring-2 ring-primary-600 ring-offset-1' : 'border-slate-200 hover:border-slate-400' }}" style="background-color:{{ $color->value }}"></a>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="space-y-2.5">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Options</p>
                    @foreach(['on_sale'=>'En promotion seulement','in_stock'=>'En stock seulement'] as $param=>$label)
                    <a href="{{ request($param) ? request()->fullUrlWithQuery([$param=>null]) : request()->fullUrlWithQuery([$param=>'1','page'=>null]) }}"
                       class="flex items-center gap-2.5 text-sm transition-colors {{ request($param) ? 'text-primary-600 font-medium' : 'text-slate-600 hover:text-slate-900' }}">
                        <span class="w-4 h-4 rounded border-2 flex items-center justify-center flex-shrink-0 {{ request($param) ? 'bg-primary-600 border-primary-600' : 'border-slate-300' }}">
                            @if(request($param))<svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $xCheck !!}</svg>@endif
                        </span>
                        {{ $label }}
                    </a>
                    @endforeach
                </div>

                <a href="{{ route('shop.index', request()->all()) }}" class="block w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg text-center transition-colors">
                    Voir les résultats ({{ $products->total() }})
                </a>
            </div>
        </aside>

        {{-- Grille produits --}}
        <div class="flex-1 min-w-0">
            @if($products->count() > 0)
            <div :class="viewMode==='grid' ? 'grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4' : 'flex flex-col gap-3'">
                @foreach($products as $product)
                @include('front.shop.partials.product-card', ['product'=>$product])
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <svg class="w-14 h-14 text-slate-200 mb-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Aucun produit trouvé</h3>
                <p class="text-sm text-slate-500 mb-6 max-w-xs">Aucun produit ne correspond à vos critères. Essayez d'élargir votre recherche.</p>
                <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition-colors">Effacer les filtres</a>
            </div>
            @endif

            @if($products->hasPages())
            <div class="mt-10">{{ $products->withQueryString()->links('vendor.pagination.tailwind') }}</div>
            @endif
        </div>
    </div>

    {{-- Drawer filtres mobile --}}
    <div x-show="filtersOpen" x-cloak x-transition.opacity @click="filtersOpen=false" class="lg:hidden fixed inset-0 bg-slate-900/50 z-[80]"></div>
    <aside x-show="filtersOpen" x-cloak
           x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
           class="lg:hidden fixed top-0 right-0 bottom-0 w-[88vw] max-w-sm bg-white z-[90] flex flex-col shadow-xl">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-900">Filtres @if($activeFilters>0)<span class="ml-1 inline-flex items-center justify-center w-5 h-5 bg-primary-600 text-white text-[10px] font-bold rounded-full">{{ $activeFilters }}</span>@endif</h2>
            <button @click="filtersOpen=false" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <form method="GET" action="{{ route('shop.index') }}" class="flex-1 overflow-y-auto p-5 space-y-6">
            @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
            @if($categories->count() > 0)
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Catégories</p>
                <ul class="space-y-1">
                    <li><label class="flex items-center gap-2.5 cursor-pointer py-1"><input type="radio" name="category" value="" {{ !request('category')?'checked':'' }} class="text-primary-600 focus:ring-primary-500"><span class="text-sm text-slate-700">Tout voir</span></label></li>
                    @foreach($categories as $cat)<li><label class="flex items-center gap-2.5 cursor-pointer py-1"><input type="radio" name="category" value="{{ $cat->slug }}" {{ request('category')===$cat->slug?'checked':'' }} class="text-primary-600 focus:ring-primary-500"><span class="text-sm text-slate-700">{{ $cat->name }}</span></label></li>@endforeach
                </ul>
            </div>
            @endif
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Prix (XOF)</p>
                <div class="flex items-center gap-2">
                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" min="0" class="flex-1 px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-primary-500">
                    <span class="text-slate-300">–</span>
                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" min="0" class="flex-1 px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-primary-500">
                </div>
            </div>
            @if(isset($colors) && $colors->count() > 0)
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Couleur</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($colors as $color)<label class="cursor-pointer" title="{{ $color->label }}"><input type="radio" name="color" value="{{ $color->id }}" {{ request('color')==$color->id?'checked':'' }} class="sr-only peer"><span class="block w-8 h-8 rounded-full border-2 peer-checked:border-primary-600 peer-checked:ring-2 peer-checked:ring-primary-600 peer-checked:ring-offset-1 border-slate-200 hover:border-slate-400 transition-all" style="background-color:{{ $color->value }}"></span></label>@endforeach
                </div>
            </div>
            @endif
            <div class="space-y-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Options</p>
                <label class="flex items-center gap-2.5 cursor-pointer"><input type="checkbox" name="on_sale" value="1" {{ request('on_sale')?'checked':'' }} class="rounded text-primary-600 focus:ring-primary-500"><span class="text-sm text-slate-700">En promotion seulement</span></label>
                <label class="flex items-center gap-2.5 cursor-pointer"><input type="checkbox" name="in_stock" value="1" {{ request('in_stock')?'checked':'' }} class="rounded text-primary-600 focus:ring-primary-500"><span class="text-sm text-slate-700">En stock seulement</span></label>
            </div>
            <button type="submit" class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl text-sm transition-colors">Voir les résultats ({{ $products->total() }})</button>
            @if($activeFilters>0)<a href="{{ route('shop.index') }}" class="block text-center text-sm text-slate-400 hover:text-slate-700 transition-colors">Effacer les filtres</a>@endif
        </form>
    </aside>
</div>

<style>.scrollbar-none::-webkit-scrollbar{display:none}.scrollbar-none{-ms-overflow-style:none;scrollbar-width:none}</style>
@endsection
