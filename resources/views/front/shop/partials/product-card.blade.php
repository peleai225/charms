<div class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300">
    <!-- Image -->
    <a href="{{ route('shop.product', $product->slug) }}" class="block relative aspect-square overflow-hidden bg-gray-100">
        @if($product->images->where('is_primary', true)->first())
            <img src="{{ asset('storage/' . $product->images->where('is_primary', true)->first()->path) }}" 
                alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif

        <!-- Badges -->
        <div class="absolute top-3 left-3 flex flex-col gap-2">
            @if($product->is_new)
                <span class="px-2 py-1 bg-blue-500 text-white text-xs font-medium rounded-full">Nouveau</span>
            @endif
            @if($product->is_on_sale)
                <span class="px-2 py-1 bg-red-500 text-white text-xs font-medium rounded-full">-{{ $product->discount_percentage }}%</span>
            @endif
        </div>

        <!-- Couleurs disponibles -->
        @php
            $colors = $product->variants->pluck('attributeValues')->flatten()->filter(fn($av) => $av->attribute?->slug === 'couleur')->unique('id');
        @endphp
        @if($colors->count() > 0)
            <div class="absolute bottom-3 left-3 flex gap-1">
                @foreach($colors->take(5) as $color)
                    <span class="w-4 h-4 rounded-full border border-white shadow-sm" 
                        style="background-color: {{ $color->color_code }}" 
                        title="{{ $color->value }}"></span>
                @endforeach
                @if($colors->count() > 5)
                    <span class="w-4 h-4 rounded-full bg-gray-200 text-xs flex items-center justify-center text-gray-600">+</span>
                @endif
            </div>
        @endif

        <!-- Action rapide -->
        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
            <span class="px-4 py-2 bg-white text-gray-900 font-medium rounded-xl shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform">
                Voir le produit
            </span>
        </div>
    </a>

    <!-- Infos -->
    <div class="p-4">
        <!-- Catégorie -->
        @if($product->category)
            <a href="{{ route('shop.category', $product->category->slug) }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                {{ $product->category->name }}
            </a>
        @endif

        <!-- Nom -->
        <h3 class="mt-1">
            <a href="{{ route('shop.product', $product->slug) }}" class="font-medium text-gray-900 hover:text-primary-600 line-clamp-2">
                {{ $product->name }}
            </a>
        </h3>

        <!-- Prix -->
        <div class="mt-2 flex items-baseline gap-2">
            <span class="text-lg font-bold text-gray-900">{{ format_price($product->sale_price) }}</span>
            @if($product->compare_price)
                <span class="text-sm text-gray-500 line-through">{{ format_price($product->compare_price) }}</span>
            @endif
        </div>

        <!-- Stock -->
        @if(!$product->is_in_stock)
            <p class="mt-2 text-xs text-red-500 font-medium">Rupture de stock</p>
        @endif
    </div>
</div>

