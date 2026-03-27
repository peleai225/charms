@php
    $img = $product->images->where('is_primary', true)->first() ?? $product->images->first();
    $hasVariants = $product->variants->count() > 0;
@endphp
<div class="group relative bg-white rounded-xl overflow-hidden border border-slate-100 hover:border-slate-200 hover:shadow-lg transition-all duration-300"
     x-data="{ adding: false, added: false }">

    {{-- Image --}}
    <a href="{{ route('shop.product', $product->slug) }}" class="block relative overflow-hidden bg-slate-50">
        <div class="aspect-[3/4]">
            @if($img)
                <img src="{{ asset('storage/' . $img->path) }}"
                    alt="{{ $product->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy">
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100">
                    <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
        </div>

        {{-- Badges --}}
        <div class="absolute top-2 left-2 flex flex-col gap-1 z-10">
            @if($product->is_new)
                <span class="px-2 py-0.5 bg-emerald-500 text-white text-[9px] font-bold uppercase tracking-wider rounded-md">Nouveau</span>
            @endif
            @if($product->is_on_sale)
                <span class="px-2 py-0.5 bg-red-500 text-white text-[9px] font-bold uppercase tracking-wider rounded-md">-{{ $product->discount_percentage }}%</span>
            @endif
        </div>

        {{-- Quick action overlay --}}
        <div class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex justify-center">
            <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/95 text-slate-800 text-xs font-semibold rounded-full shadow-sm backdrop-blur-sm transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Aperçu
            </span>
        </div>
    </a>

    {{-- Product info --}}
    <div class="p-3">
        @if($product->category)
            <a href="{{ route('shop.category', $product->category->slug) }}" class="text-[10px] text-slate-400 font-medium uppercase tracking-wider hover:text-primary-600 transition-colors">
                {{ $product->category->name }}
            </a>
        @endif

        <h3 class="mt-0.5 mb-2">
            <a href="{{ route('shop.product', $product->slug) }}" class="font-semibold text-sm text-slate-800 hover:text-primary-600 line-clamp-1 transition-colors">
                {{ $product->name }}
            </a>
        </h3>

        <div class="flex items-center justify-between gap-2">
            <div class="flex items-baseline gap-1.5">
                <span class="text-base font-bold text-slate-900">{{ format_price($product->sale_price) }}</span>
                @if($product->compare_price)
                    <span class="text-[11px] text-slate-400 line-through">{{ format_price($product->compare_price) }}</span>
                @endif
            </div>

            @if(!$product->is_in_stock)
                <span class="text-[9px] text-red-500 font-bold uppercase">Rupture</span>
            @elseif(!$hasVariants)
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
                                setTimeout(() => added = false, 2000);
                            }
                        })
                        .catch(() => { adding = false; })
                    "
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200"
                    :class="added ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500 hover:bg-primary-600 hover:text-white'"
                    :disabled="adding">
                    <template x-if="adding">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </template>
                    <template x-if="!adding && added">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <template x-if="!adding && !added">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </template>
                </button>
            @else
                <a href="{{ route('shop.product', $product->slug) }}"
                   class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-primary-600 hover:text-white flex items-center justify-center transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </a>
            @endif
        </div>
    </div>
</div>
