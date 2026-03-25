<div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:shadow-slate-200/80 transition-all duration-400 hover:-translate-y-1.5 border border-slate-100/80 relative"
     x-data="{ adding: false, added: false }">
    <!-- Image -->
    <a href="{{ route('shop.product', $product->slug) }}" class="block relative aspect-[4/5] overflow-hidden bg-slate-50">
        @if($product->images->where('is_primary', true)->first())
            <img src="{{ asset('storage/' . $product->images->where('is_primary', true)->first()->path) }}"
                alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out"
                loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100">
                <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif

        <!-- Badges -->
        <div class="absolute top-2.5 left-2.5 flex flex-col gap-1.5 z-10">
            @if($product->is_new)
                <span class="px-2.5 py-1 bg-emerald-500 text-white text-[10px] font-bold uppercase tracking-wider rounded-md shadow-sm">Nouveau</span>
            @endif
            @if($product->is_on_sale)
                <span class="px-2.5 py-1 bg-red-500 text-white text-[10px] font-bold uppercase tracking-wider rounded-md shadow-sm">-{{ $product->discount_percentage }}%</span>
            @endif
        </div>

        <!-- Couleurs disponibles -->
        @php
            $colors = $product->variants->pluck('attributeValues')->flatten()->filter(fn($av) => $av->attribute?->slug === 'couleur')->unique('id');
            $hasVariants = $product->variants->count() > 0;
        @endphp
        @if($colors->count() > 0)
            <div class="absolute bottom-2.5 left-2.5 flex gap-1 z-10">
                @foreach($colors->take(4) as $color)
                    <span class="w-3.5 h-3.5 rounded-full border-2 border-white shadow-sm"
                        style="background-color: {{ $color->color_code }}"
                        title="{{ $color->value }}"></span>
                @endforeach
                @if($colors->count() > 4)
                    <span class="w-3.5 h-3.5 rounded-full bg-white/90 backdrop-blur text-[8px] flex items-center justify-center text-slate-600 font-bold shadow-sm">+{{ $colors->count() - 4 }}</span>
                @endif
            </div>
        @endif

        <!-- Hover overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-400 flex items-end justify-center pb-4">
            <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-white text-slate-900 font-semibold rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-400 text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Voir le produit
            </span>
        </div>
    </a>

    <!-- Infos -->
    <div class="p-3.5 sm:p-4">
        <!-- Catégorie -->
        @if($product->category)
            <a href="{{ route('shop.category', $product->category->slug) }}" class="text-[10px] text-slate-400 hover:text-primary-600 font-semibold uppercase tracking-widest transition-colors">
                {{ $product->category->name }}
            </a>
        @endif

        <!-- Nom -->
        <h3 class="mt-1">
            <a href="{{ route('shop.product', $product->slug) }}" class="font-semibold text-sm text-slate-800 hover:text-primary-600 line-clamp-2 transition-colors leading-snug">
                {{ $product->name }}
            </a>
        </h3>

        <!-- Prix + Actions -->
        <div class="mt-2.5 flex items-end justify-between gap-2">
            <div class="flex items-baseline gap-1.5 flex-wrap">
                <span class="text-base font-extrabold text-slate-900">{{ format_price($product->sale_price) }}</span>
                @if($product->compare_price)
                    <span class="text-xs text-slate-400 line-through">{{ format_price($product->compare_price) }}</span>
                @endif
            </div>
            @if(!$product->is_in_stock)
                <span class="text-[10px] text-red-500 font-semibold uppercase tracking-wide whitespace-nowrap">Rupture</span>
            @elseif(!$hasVariants)
                {{-- Ajout rapide au panier (produits sans variantes) --}}
                <button
                    @click.prevent="
                        if (adding) return;
                        adding = true;
                        fetch('{{ route('cart.add') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ product_id: {{ $product->id }}, quantity: 1 })
                        })
                        .then(r => r.json())
                        .then(data => {
                            adding = false;
                            if (data.success !== false) {
                                added = true;
                                if ($store.cart) $store.cart.count = data.cart_count || ($store.cart.count + 1);
                                if ($store.cartDrawer) $store.cartDrawer.open();
                                $dispatch('show-notification', { message: 'Produit ajouté au panier', type: 'success' });
                                setTimeout(() => added = false, 2000);
                            } else {
                                $dispatch('show-notification', { message: data.message || 'Erreur', type: 'error' });
                            }
                        })
                        .catch(() => { adding = false; $dispatch('show-notification', { message: 'Erreur réseau', type: 'error' }); })
                    "
                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300"
                    :class="added ? 'bg-emerald-500 text-white scale-110' : 'bg-slate-100 text-slate-500 hover:bg-primary-600 hover:text-white hover:scale-110'"
                    :disabled="adding"
                    title="Ajouter au panier"
                >
                    <template x-if="adding">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </template>
                    <template x-if="!adding && added">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <template x-if="!adding && !added">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </template>
                </button>
            @else
                {{-- Produit avec variantes : lien vers la page produit --}}
                <a href="{{ route('shop.product', $product->slug) }}"
                   class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-primary-600 hover:text-white hover:scale-110 flex items-center justify-center transition-all duration-300"
                   title="Choisir les options">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </a>
            @endif
        </div>
    </div>
</div>
