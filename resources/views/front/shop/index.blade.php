@extends('layouts.front')

@section('title', 'Boutique')

@section('content')
<!-- Hero Banner -->
<div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white py-10 mb-8 relative overflow-hidden">
    <div class="absolute -top-16 -right-16 w-64 h-64 bg-primary-600/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-16 -left-16 w-72 h-72 bg-violet-600/8 rounded-full blur-3xl"></div>
    <div class="container mx-auto px-4 relative">
        <nav class="text-sm text-slate-400 mb-4 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <span class="text-white font-medium">Boutique</span>
        </nav>
        <h1 class="text-3xl font-extrabold">Notre Boutique</h1>
        <p class="text-slate-300 text-sm mt-1">Explorez notre collection de produits</p>
    </div>
</div>

<div class="container mx-auto px-4 pb-10">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filtres -->
        <aside class="lg:w-64 flex-shrink-0">
            <form method="GET" action="{{ route('shop.index') }}" class="space-y-5">
                <!-- Recherche -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100">
                    <div class="h-1 bg-gradient-to-r from-primary-500/20 to-transparent rounded-t-2xl"></div>
                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900 mb-3">Recherche</h3>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                        >
                    </div>
                </div>

                <!-- Catégories -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100">
                    <div class="h-1 bg-gradient-to-r from-primary-500/20 to-transparent rounded-t-2xl"></div>
                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900 mb-3">Catégories</h3>
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
                </div>

                <!-- Prix -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100">
                    <div class="h-1 bg-gradient-to-r from-primary-500/20 to-transparent rounded-t-2xl"></div>
                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900 mb-3">Prix</h3>
                        <div class="flex gap-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="{{ floor($priceRange->min ?? 0) }}"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                            <span class="text-slate-400 self-center">-</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="{{ ceil($priceRange->max ?? 1000) }}"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                        </div>
                    </div>
                </div>

                <!-- Couleurs -->
                @if($colors->count() > 0)
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100">
                    <div class="h-1 bg-gradient-to-r from-primary-500/20 to-transparent rounded-t-2xl"></div>
                    <div class="p-5">
                        <h3 class="font-semibold text-gray-900 mb-3">Couleurs</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($colors as $color)
                                <label class="cursor-pointer">
                                    <input type="radio" name="color" value="{{ $color->slug }}" class="sr-only peer"
                                        {{ request('color') === $color->slug ? 'checked' : '' }}>
                                    <span class="block w-8 h-8 rounded-full border-2 border-transparent peer-checked:border-slate-900 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary-500 transition-all"
                                        style="background-color: {{ $color->color_code }}"
                                        title="{{ $color->value }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <button type="submit" class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary-500/25 hover:-translate-y-0.5 inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtrer
                </button>

                @if(request()->hasAny(['search', 'category', 'min_price', 'max_price', 'color']))
                    <a href="{{ route('shop.index') }}" class="block text-center text-sm text-slate-500 hover:text-primary-600 transition-colors">
                        Effacer les filtres
                    </a>
                @endif
            </form>
        </aside>

        <!-- Produits -->
        <div class="flex-1">
            <!-- En-tête -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <p class="text-slate-600 font-medium">{{ $products->total() }} produit(s)</p>

                <div class="relative">
                    <select onchange="window.location.href = this.value" class="appearance-none pl-4 pr-10 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 bg-white text-slate-700 font-medium cursor-pointer">
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Plus récents</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}" {{ request('sort') === 'popular' ? 'selected' : '' }}>Populaires</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                        </svg>
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
                        <h3 class="text-xl font-semibold text-slate-900 mb-2">Aucun produit trouvé</h3>
                        <p class="text-slate-500 text-sm mb-6 max-w-sm">Essayez de modifier vos filtres ou parcourez nos catégories.</p>
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
    </div>
</div>
@endsection
