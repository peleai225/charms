@php $isMobile = $mobile ?? false; @endphp
<form method="GET" action="{{ route('shop.index') }}" class="space-y-6">
    @if($isMobile && request('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif

    {{-- RECHERCHE --}}
    <div>
        <h3 class="font-medium text-stone-900 mb-3 text-[11px] uppercase tracking-[0.2em]">— Recherche</h3>
        <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un produit..."
                class="w-full pl-10 pr-4 py-2.5 border border-stone-200 rounded-full focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm bg-white">
        </div>
    </div>

    {{-- CATÉGORIES --}}
    @if($categories->count() > 0)
    <div>
        <h3 class="font-medium text-stone-900 mb-3 text-[11px] uppercase tracking-[0.2em]">— Catégories</h3>
        <div class="space-y-0.5 max-h-72 overflow-y-auto -mx-2">
            <label class="flex items-center gap-3 cursor-pointer hover:bg-stone-50 px-2 py-2 rounded-lg transition-colors">
                <input type="radio" name="category" value=""
                    {{ !request('category') ? 'checked' : '' }}
                    class="w-4 h-4 text-primary-600 focus:ring-primary-500 border-stone-300">
                <span class="text-sm text-stone-700">Toutes</span>
            </label>
            @foreach($categories as $category)
            <label class="flex items-center gap-3 cursor-pointer hover:bg-stone-50 px-2 py-2 rounded-lg transition-colors">
                <input type="radio" name="category" value="{{ $category->slug }}"
                    {{ request('category') === $category->slug ? 'checked' : '' }}
                    class="w-4 h-4 text-primary-600 focus:ring-primary-500 border-stone-300">
                <span class="text-sm text-stone-700 flex-1">{{ $category->name }}</span>
                <span class="text-xs text-stone-400 tabular-nums">{{ $category->products_count ?? 0 }}</span>
            </label>
            @endforeach
        </div>
    </div>
    @endif

    {{-- PRIX --}}
    <div>
        <h3 class="font-medium text-stone-900 mb-3 text-[11px] uppercase tracking-[0.2em]">— Prix (F CFA)</h3>
        <div class="flex gap-2 items-center">
            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="{{ floor($priceRange->min ?? 0) }}"
                class="w-full px-3 py-2.5 border border-stone-200 rounded-full text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 bg-white">
            <span class="text-stone-300 text-sm">—</span>
            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="{{ ceil($priceRange->max ?? 1000) }}"
                class="w-full px-3 py-2.5 border border-stone-200 rounded-full text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 bg-white">
        </div>
    </div>

    {{-- COULEURS --}}
    @if($colors->count() > 0)
    <div>
        <h3 class="font-medium text-stone-900 mb-3 text-[11px] uppercase tracking-[0.2em]">— Couleurs</h3>
        <div class="flex flex-wrap gap-2.5">
            @foreach($colors as $color)
            <label class="cursor-pointer relative" title="{{ $color->value }}">
                <input type="radio" name="color" value="{{ $color->slug }}" class="sr-only peer"
                    {{ request('color') === $color->slug ? 'checked' : '' }}>
                <span class="block w-9 h-9 rounded-full border-2 border-white ring-1 ring-stone-200 peer-checked:ring-2 peer-checked:ring-primary-500 peer-checked:ring-offset-2 hover:scale-110 transition-all shadow-sm"
                    style="background-color: {{ $color->color_code }}"></span>
            </label>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Boutons d'action --}}
    <div class="{{ $isMobile ? 'sticky bottom-0 bg-white pt-4 pb-1 -mx-5 px-5 border-t border-stone-100' : 'pt-2' }} space-y-2.5">
        <button type="submit" class="w-full py-3 bg-stone-900 hover:bg-stone-800 text-white font-medium rounded-full transition-all inline-flex items-center justify-center gap-2 text-sm">
            Appliquer
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </button>
        @if(request()->hasAny(['search', 'category', 'min_price', 'max_price', 'color']))
            <a href="{{ route('shop.index') }}" class="block w-full py-2.5 text-center text-sm text-stone-500 hover:text-stone-900 font-medium transition-colors">
                Réinitialiser
            </a>
        @endif
    </div>
</form>
