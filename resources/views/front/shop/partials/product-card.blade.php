<div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl hover:shadow-primary-100/60 transition-all duration-500 hover:-translate-y-1 border border-slate-100">
    <!-- Image -->
    <a href="{{ route('shop.product', $product->slug) }}" class="block relative aspect-square overflow-hidden bg-slate-50">
        @if($product->images->where('is_primary', true)->first())
            <img src="{{ asset('storage/' . $product->images->where('is_primary', true)->first()->path) }}"
                alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif

        <!-- Gradient overlay on hover -->
        <div class="absolute inset-0 bg-gradient-to-t from-primary-600/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

        <!-- Badges -->
        <div class="absolute top-3 left-3 flex flex-col gap-1.5">
            @if($product->is_new)
                <span class="px-2.5 py-1 backdrop-blur-sm bg-primary-600/90 text-white text-xs font-semibold rounded-lg shadow-sm ring-1 ring-white/20">Nouveau</span>
            @endif
            @if($product->is_on_sale)
                <span class="px-2.5 py-1 backdrop-blur-sm bg-red-500/90 text-white text-xs font-semibold rounded-lg shadow-sm ring-1 ring-white/20">-{{ $product->discount_percentage }}%</span>
            @endif
        </div>

        <!-- Couleurs disponibles -->
        @php
            $colors = $product->variants->pluck('attributeValues')->flatten()->filter(fn($av) => $av->attribute?->slug === 'couleur')->unique('id');
        @endphp
        @if($colors->count() > 0)
            <div class="absolute bottom-3 left-3 flex gap-1.5">
                @foreach($colors->take(5) as $color)
                    <span class="w-4 h-4 rounded-full border-2 border-white shadow-sm ring-1 ring-slate-200/50"
                        style="background-color: {{ $color->color_code }}"
                        title="{{ $color->value }}"></span>
                @endforeach
                @if($colors->count() > 5)
                    <span class="w-4 h-4 rounded-full bg-slate-200 text-[10px] flex items-center justify-center text-slate-600 font-medium">+</span>
                @endif
            </div>
        @endif

        <!-- Hover overlay with pill button -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
            <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/95 backdrop-blur-sm text-slate-900 font-medium rounded-full shadow-lg transform translate-y-3 group-hover:translate-y-0 transition-transform duration-300 text-sm">
                Voir le produit
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>

    <!-- Infos -->
    <div class="p-4 pt-3">
        <!-- Catégorie -->
        @if($product->category)
            <a href="{{ route('shop.category', $product->category->slug) }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium uppercase tracking-wide">
                {{ $product->category->name }}
            </a>
        @endif

        <!-- Nom -->
        <h3 class="mt-1.5">
            <a href="{{ route('shop.product', $product->slug) }}" class="font-medium text-slate-900 hover:text-primary-600 line-clamp-2 transition-colors leading-snug">
                {{ $product->name }}
            </a>
        </h3>

        <!-- Prix -->
        <div class="mt-3 flex items-baseline gap-2 flex-wrap">
            <span class="text-lg font-bold text-primary-600">{{ format_price($product->sale_price) }}</span>
            @if($product->compare_price)
                <span class="text-sm text-slate-400 line-through">{{ format_price($product->compare_price) }}</span>
            @endif
        </div>

        <!-- Stock -->
        @if(!$product->is_in_stock)
            <p class="mt-2 text-xs text-red-600 font-medium">Rupture de stock</p>
        @endif
    </div>

    <!-- Bottom accent line -->
    <div class="h-0.5 bg-gradient-to-r from-primary-500 to-accent-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
</div>
