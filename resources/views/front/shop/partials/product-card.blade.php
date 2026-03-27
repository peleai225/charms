@php
    $img = $product->images->where('is_primary', true)->first() ?? $product->images->first();
    $hasVariants = $product->variants->count() > 0;
    $discountPct = $product->discount_percentage ?? 0;
@endphp
<div class="group relative bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1"
     x-data="{ adding: false, added: false, hovered: false }"
     @mouseenter="hovered = true" @mouseleave="hovered = false">

    {{-- Image Container --}}
    <a href="{{ route('shop.product', $product->slug) }}" class="block relative overflow-hidden">
        <div class="aspect-[4/5] bg-gradient-to-br from-slate-50 to-slate-100">
            @if($img)
                <img src="{{ asset('storage/' . $img->path) }}"
                    alt="{{ $product->name }}"
                    class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
                    loading="lazy">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    <div class="text-center">
                        <svg class="w-10 h-10 text-slate-200 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            @endif
        </div>

        {{-- Badges top-left --}}
        <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10">
            @if($product->is_new)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-500 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg shadow-lg shadow-emerald-500/25">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Nouveau
                </span>
            @endif
            @if($product->is_on_sale && $discountPct > 0)
                <span class="inline-flex items-center px-2.5 py-1 bg-red-500 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg shadow-lg shadow-red-500/25 animate-pulse">
                    -{{ $discountPct }}%
                </span>
            @endif
        </div>

        {{-- Wishlist heart top-right --}}
        <button class="absolute top-3 right-3 z-10 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-red-50 hover:scale-110 shadow-sm"
                @click.prevent>
            <svg class="w-4 h-4 text-slate-400 hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </button>

        {{-- Hover overlay with quick view --}}
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500">
            <div class="absolute bottom-4 inset-x-4 flex justify-center">
                <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-slate-900 text-xs font-bold rounded-xl shadow-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-500 hover:bg-primary-600 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Aperçu rapide
                </span>
            </div>
        </div>

        {{-- Out of stock overlay --}}
        @if(!$product->is_in_stock)
        <div class="absolute inset-0 bg-white/60 backdrop-blur-[2px] flex items-center justify-center z-10">
            <span class="px-4 py-2 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest rounded-lg">Rupture de stock</span>
        </div>
        @endif
    </a>

    {{-- Product Info --}}
    <div class="p-4">
        {{-- Category --}}
        @if($product->category)
            <a href="{{ route('shop.category', $product->category->slug) }}"
               class="inline-block text-[10px] text-primary-500 font-semibold uppercase tracking-widest hover:text-primary-700 transition-colors mb-1.5">
                {{ $product->category->name }}
            </a>
        @endif

        {{-- Name --}}
        <h3 class="mb-3">
            <a href="{{ route('shop.product', $product->slug) }}"
               class="font-bold text-sm text-slate-900 hover:text-primary-600 line-clamp-2 transition-colors leading-snug">
                {{ $product->name }}
            </a>
        </h3>

        {{-- Price row --}}
        <div class="flex items-end justify-between gap-2">
            <div class="flex items-baseline gap-2">
                <span class="text-lg font-extrabold text-slate-900">{{ format_price($product->sale_price) }}</span>
                @if($product->compare_price)
                    <span class="text-xs text-slate-400 line-through font-medium">{{ format_price($product->compare_price) }}</span>
                @endif
            </div>

            {{-- Add to cart --}}
            @if($product->is_in_stock)
                @if(!$hasVariants)
                <button
                    @click.prevent="
                        if (adding) return;
                        adding = true;
                        fetch('{{ route('cart.add') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ product_id: {{ $product->id }}, quantity: 1 })
                        })
                        .then(r => r.json())
                        .then(data => {
                            adding = false;
                            if (data.success !== false) {
                                added = true;
                                if ($store.cart) $store.cart.count = data.cart_count || ($store.cart.count + 1);
                                if ($store.cartDrawer) $store.cartDrawer.open();
                                $dispatch('show-notification', { message: 'Ajouté au panier', type: 'success' });
                                setTimeout(() => added = false, 2500);
                            }
                        })
                        .catch(() => { adding = false; })
                    "
                    class="group/btn relative w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 overflow-hidden"
                    :class="added
                        ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 scale-110'
                        : 'bg-primary-50 text-primary-600 hover:bg-primary-600 hover:text-white hover:shadow-lg hover:shadow-primary-600/25 hover:scale-105'"
                    :disabled="adding">
                    <template x-if="adding">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </template>
                    <template x-if="!adding && added">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <template x-if="!adding && !added">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </template>
                </button>
                @else
                <a href="{{ route('shop.product', $product->slug) }}"
                   class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 hover:bg-primary-600 hover:text-white flex items-center justify-center transition-all duration-300 hover:shadow-lg hover:shadow-primary-600/25 hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                @endif
            @endif
        </div>
    </div>
</div>
