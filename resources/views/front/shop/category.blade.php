@extends('layouts.front')

@section('title', $category->name . ' - Boutique')

@section('content')
<!-- Hero Banner -->
<div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white py-10 mb-8 relative overflow-hidden">
    <div class="absolute -top-16 -right-16 w-64 h-64 bg-primary-600/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-16 -left-16 w-72 h-72 bg-violet-600/8 rounded-full blur-3xl"></div>
    <div class="container mx-auto px-4 relative">
        <nav class="text-sm text-slate-400 mb-4 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('shop.index') }}" class="hover:text-white transition-colors">Boutique</a>
            @if($category->parent)
                <span class="text-slate-600">/</span>
                <a href="{{ route('shop.category', $category->parent->slug) }}" class="hover:text-white transition-colors">{{ $category->parent->name }}</a>
            @endif
            <span class="text-slate-600">/</span>
            <span class="text-white font-medium">{{ $category->name }}</span>
        </nav>
        <h1 class="text-3xl font-extrabold">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-slate-300 text-sm mt-1 max-w-xl">{{ $category->description }}</p>
        @endif
    </div>
</div>

<div class="container mx-auto px-4 pb-10">
    <!-- Sous-catégories -->
    @if($subcategories->count() > 0)
    <div class="mb-10">
        <h2 class="text-lg font-semibold text-slate-900 mb-5">Sous-catégories</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-6">
            @foreach($subcategories as $subcategory)
                <a href="{{ route('shop.category', $subcategory->slug) }}"
                   class="group bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-xl hover:shadow-primary-100/50 hover:-translate-y-1 transition-all duration-300">
                    @if($subcategory->image)
                        <div class="relative aspect-[4/3] bg-slate-100 overflow-hidden">
                            <img src="{{ asset('storage/' . $subcategory->image) }}"
                                 alt="{{ $subcategory->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent"></div>
                            <div class="absolute inset-0 flex items-end p-4">
                                <div>
                                    <h3 class="font-semibold text-white text-base drop-shadow-sm">
                                        {{ $subcategory->name }}
                                    </h3>
                                    @if($subcategory->description)
                                        <p class="text-sm text-white/80 mt-0.5 line-clamp-1">{{ $subcategory->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-5">
                            <h3 class="font-semibold text-slate-900 group-hover:text-primary-600 transition-colors">
                                {{ $subcategory->name }}
                            </h3>
                            @if($subcategory->description)
                                <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $subcategory->description }}</p>
                            @endif
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Produits -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <p class="text-slate-600 font-medium">{{ $products->total() }} produit(s) dans cette catégorie</p>

            <div class="relative">
                <select onchange="window.location.href = this.value"
                        class="appearance-none pl-4 pr-10 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 bg-white text-slate-700 font-medium cursor-pointer">
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}"
                            {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Plus récents</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}"
                            {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}"
                            {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}"
                            {{ request('sort') === 'popular' ? 'selected' : '' }}>Populaires</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Grille produits -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-6">
        @forelse($products as $product)
            @include('front.shop.partials.product-card', ['product' => $product])
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-20 px-4">
                <div class="w-24 h-24 rounded-2xl bg-slate-100 flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">Aucun produit dans cette catégorie</h3>
                <p class="text-slate-500 text-sm mb-6">Découvrez nos autres produits.</p>
                <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-500/25 hover:-translate-y-0.5 text-sm">
                    Voir tous les produits
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
