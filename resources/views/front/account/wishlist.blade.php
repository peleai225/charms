@extends('layouts.front')

@section('title', 'Ma liste de souhaits')

@section('content')

<div class="bg-slate-50 border-b border-slate-200 py-8">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-slate-400 mb-2 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-slate-700 transition-colors">Accueil</a>
            <span>/</span>
            <a href="{{ route('account.dashboard') }}" class="hover:text-slate-700 transition-colors">Mon compte</a>
            <span>/</span>
            <span class="text-slate-700">Liste de souhaits</span>
        </nav>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-slate-900">Ma liste de souhaits</h1>
            @if($wishlistItems->count() > 0)
            <span class="inline-flex px-2.5 py-0.5 bg-slate-200 text-slate-600 text-sm font-semibold rounded-full">
                {{ $wishlistItems->total() }}
            </span>
            @endif
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('front.account.partials.sidebar')

            <div class="flex-1 min-w-0">

                @if (session('success'))
                <div class="mb-5 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
                    {{ session('success') }}
                </div>
                @endif

                @if($wishlistItems->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($wishlistItems as $item)
                    @php $product = $item->product; @endphp
                    @if($product)
                    @php
                        $img = $product->images->where('is_primary', true)->first() ?? $product->images->first();
                    @endphp
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-primary-200 hover:shadow-sm transition-all group">
                        <a href="{{ route('shop.product', $product->slug) }}" class="block relative">
                            <div class="aspect-[4/5] bg-slate-50">
                                @if($img)
                                    <img src="{{ asset('storage/' . $img->path) }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                         loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </a>
                        <div class="p-3">
                            <a href="{{ route('shop.product', $product->slug) }}"
                               class="text-sm font-medium text-slate-900 hover:text-primary-600 line-clamp-2 leading-snug block mb-2 transition-colors">
                                {{ $product->name }}
                            </a>
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-900 text-sm">{{ format_price($product->sale_price) }}</span>
                                <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="Retirer des favoris"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>

                @if($wishlistItems->hasPages())
                <div class="mt-6">
                    {{ $wishlistItems->links() }}
                </div>
                @endif

                @else
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Votre liste de souhaits est vide</h3>
                    <p class="text-slate-500 text-sm mb-6 max-w-sm mx-auto">
                        Vous n'avez pas encore ajouté de produits à vos favoris. Cliquez sur le coeur d'un produit pour l'ajouter.
                    </p>
                    <a href="{{ route('shop.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors">
                        Découvrir la boutique
                    </a>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
