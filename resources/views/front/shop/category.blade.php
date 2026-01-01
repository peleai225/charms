@extends('layouts.front')

@section('title', $category->name . ' - Boutique')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-primary-600">Accueil</a>
        <span class="mx-2">/</span>
        <a href="{{ route('shop.index') }}" class="hover:text-primary-600">Boutique</a>
        @if($category->parent)
            <span class="mx-2">/</span>
            <a href="{{ route('shop.category', $category->parent->slug) }}" class="hover:text-primary-600">{{ $category->parent->name }}</a>
        @endif
        <span class="mx-2">/</span>
        <span class="text-gray-900">{{ $category->name }}</span>
    </nav>

    <!-- En-tête de la catégorie -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-gray-600">{{ $category->description }}</p>
        @endif
    </div>

    <!-- Sous-catégories -->
    @if($subcategories->count() > 0)
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Sous-catégories</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($subcategories as $subcategory)
                <a href="{{ route('shop.category', $subcategory->slug) }}" 
                   class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:border-primary-500 hover:shadow-md transition-all group">
                    @if($subcategory->image)
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-3">
                            <img src="{{ asset('storage/' . $subcategory->image) }}" 
                                 alt="{{ $subcategory->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        </div>
                    @endif
                    <h3 class="font-medium text-gray-900 group-hover:text-primary-600 transition-colors">
                        {{ $subcategory->name }}
                    </h3>
                    @if($subcategory->description)
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $subcategory->description }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Produits -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <p class="text-gray-600">{{ $products->total() }} produit(s) dans cette catégorie</p>
            
            <select onchange="window.location.href = this.value" 
                    class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" 
                        {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Plus récents</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" 
                        {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" 
                        {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                <option value="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}" 
                        {{ request('sort') === 'popular' ? 'selected' : '' }}>Populaires</option>
            </select>
        </div>
    </div>

    <!-- Grille produits -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        @forelse($products as $product)
            @include('front.shop.partials.product-card', ['product' => $product])
        @empty
            <div class="col-span-full text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500 mb-2">Aucun produit dans cette catégorie</p>
                <a href="{{ route('shop.index') }}" class="text-primary-600 hover:text-primary-700 font-medium">
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

