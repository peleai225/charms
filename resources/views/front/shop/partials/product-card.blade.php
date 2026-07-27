@php
    $img        = $product->images->where('is_primary', true)->first() ?? $product->images->first();
    $discountPct = $product->discount_percentage ?? 0;
    $hasVariants = $product->has_variants ?? ($product->variants->count() > 0);
@endphp
<div class="group relative bg-white rounded-xl border border-slate-100 hover:border-slate-200 hover:shadow-md transition-all duration-200 overflow-hidden"
     x-data="{ adding: false, added: false }">

    {{-- Zone image --}}
    <a href="{{ route('shop.product', $product->slug) }}" class="block">
        <div class="relative aspect-[4/3] bg-slate-50 overflow-hidden">
            @if($img)
                <img src="{{ asset('storage/' . $img->path) }}"
                     alt="{{ $product->name }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                     loading="lazy">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif

            {{-- Badges --}}
            <div class="absolute top-2 left-2 flex flex-col gap-1 z-10">
                @if($product->is_on_sale && $discountPct > 0)
                    <span class="px-2 py-0.5 bg-red-500 text-white text-[10px] font-bold rounded">-{{ $discountPct }}%</span>
                @endif
                @if(!empty($product->is_new) && $product->is_new)
                    <span class="px-2 py-0.5 bg-primary-600 text-white text-[10px] font-bold rounded">Nouveau</span>
                @endif
            </div>

            {{-- Bouton favoris --}}
            <form method="POST" action="{{ route('wishlist.toggle', $product->id) }}"
                  class="absolute top-2 right-2 z-10" @click.stop>
                @csrf
                <button type="submit"
                        class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-slate-400 hover:text-red-500 shadow-sm transition-colors"
                        aria-label="Ajouter aux favoris">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>
            </form>

            {{-- Overlay CTA (hover) --}}
            <div class="absolute bottom-0 inset-x-0 p-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-10">
                @if($product->is_in_stock)
                    @if(!$hasVariants)
                    <button type="button"
                            @click.prevent="
                                if(adding) return; adding = true;
                                fetch('{{ route('cart.add') }}', {
                                    method: 'POST',
                                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},
                                    body: JSON.stringify({product_id: {{ $product->id }}, quantity: 1})
                                }).then(r=>r.json()).then(d=>{
                                    adding = false;
                                    if (d.success !== false) {
                                        added = true;
                                        if ($store.cart) $store.cart.count = d.cart_count;
                                        $dispatch('open-cart-drawer');
                                        setTimeout(() => added = false, 2000);
                                    }
                                }).catch(() => { adding = false; })"
                            :class="added ? 'bg-emerald-500 text-white' : 'bg-primary-600 hover:bg-primary-700 text-white'"
                            :disabled="adding"
                            class="w-full py-2 text-sm font-medium rounded-lg transition-colors">
                        <span x-show="!adding && !added">Ajouter</span>
                        <span x-show="adding">
                            <svg class="w-4 h-4 animate-spin inline" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>
                        <span x-show="added">Ajouté !</span>
                    </button>
                    @else
                    <a href="{{ route('shop.product', $product->slug) }}"
                       class="block w-full py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg text-center transition-colors">
                        Choisir les options
                    </a>
                    @endif
                @else
                    <button disabled
                            class="w-full py-2 bg-slate-200 text-slate-400 text-sm font-medium rounded-lg cursor-not-allowed">
                        Indisponible
                    </button>
                @endif
            </div>

            {{-- Rupture overlay --}}
            @if(!$product->is_in_stock)
            <div class="absolute inset-0 bg-white/70 flex items-center justify-center z-[5]">
                <span class="px-3 py-1.5 bg-slate-800 text-white text-xs font-semibold rounded-lg">Rupture de stock</span>
            </div>
            @endif
        </div>
    </a>

    {{-- Infos --}}
    <div class="p-3">
        @if($product->category)
            <p class="text-xs text-slate-400">{{ $product->category->name }}</p>
        @endif
        <h3 class="font-semibold text-slate-900 text-sm line-clamp-2 mt-0.5 leading-snug">
            <a href="{{ route('shop.product', $product->slug) }}" class="hover:text-primary-600 transition-colors">
                {{ $product->name }}
            </a>
        </h3>
        <div class="flex items-center gap-2 mt-1">
            <span class="font-bold text-slate-900">{{ format_price($product->sale_price) }}</span>
            @if($product->compare_price)
                <span class="text-xs text-slate-400 line-through">{{ format_price($product->compare_price) }}</span>
            @endif
        </div>
        @if(!$product->is_in_stock)
            <p class="text-xs text-slate-400 mt-1">Rupture de stock</p>
        @endif
    </div>
</div>
