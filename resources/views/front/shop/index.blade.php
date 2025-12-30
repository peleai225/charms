@extends('layouts.front')

@section('title', 'Boutique')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-primary-600">Accueil</a>
        <span class="mx-2">/</span>
        <span class="text-gray-900">Boutique</span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filtres -->
        <aside class="lg:w-64 flex-shrink-0">
            <form method="GET" action="{{ route('shop.index') }}" class="space-y-6">
                <!-- Recherche -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-3">Recherche</h3>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                </div>

                <!-- Catégories -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-3">Catégories</h3>
                    <div class="space-y-2">
                        @foreach($categories as $category)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="category" value="{{ $category->slug }}" 
                                    {{ request('category') === $category->slug ? 'checked' : '' }}
                                    class="text-primary-600 focus:ring-primary-500">
                                <span class="text-gray-700">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Prix -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-3">Prix</h3>
                    <div class="flex gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="{{ floor($priceRange->min ?? 0) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <span class="text-gray-400 self-center">-</span>
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="{{ ceil($priceRange->max ?? 1000) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>

                <!-- Couleurs -->
                @if($colors->count() > 0)
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-3">Couleurs</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($colors as $color)
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="{{ $color->slug }}" class="sr-only peer"
                                    {{ request('color') === $color->slug ? 'checked' : '' }}>
                                <span class="block w-8 h-8 rounded-full border-2 border-transparent peer-checked:border-gray-900 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary-500 transition-all"
                                    style="background-color: {{ $color->color_code }}"
                                    title="{{ $color->value }}"></span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <button type="submit" class="w-full py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                    Filtrer
                </button>

                @if(request()->hasAny(['search', 'category', 'min_price', 'max_price', 'color']))
                    <a href="{{ route('shop.index') }}" class="block text-center text-sm text-gray-500 hover:text-primary-600">
                        Effacer les filtres
                    </a>
                @endif
            </form>
        </aside>

        <!-- Produits -->
        <div class="flex-1">
            <!-- En-tête -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <p class="text-gray-600">{{ $products->total() }} produit(s)</p>
                
                <select onchange="window.location.href = this.value" class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Plus récents</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}" {{ request('sort') === 'popular' ? 'selected' : '' }}>Populaires</option>
                </select>
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
                        <p class="text-gray-500">Aucun produit trouvé</p>
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
    </div>
</div>
@endsection

