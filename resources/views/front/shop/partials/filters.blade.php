@php $isMobile = $mobile ?? false; @endphp
<form method="GET" action="{{ route('shop.index') }}" class="space-y-5">
    @if($isMobile && request('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif

    {{-- Recherche --}}
    <div class="{{ $isMobile ? '' : 'bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100' }}">
        @unless($isMobile)<div class="h-1 bg-gradient-to-r from-primary-500/20 to-transparent rounded-t-2xl"></div>@endunless
        <div class="{{ $isMobile ? '' : 'p-5' }}">
            <h3 class="font-bold text-slate-900 mb-3 text-sm uppercase tracking-wider">Recherche</h3>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un produit..."
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm">
            </div>
        </div>
    </div>

    {{-- Catégories --}}
    @if($categories->count() > 0)
    <div class="{{ $isMobile ? '' : 'bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100' }}">
        @unless($isMobile)<div class="h-1 bg-gradient-to-r from-primary-500/20 to-transparent rounded-t-2xl"></div>@endunless
        <div class="{{ $isMobile ? '' : 'p-5' }}">
            <h3 class="font-bold text-slate-900 mb-3 text-sm uppercase tracking-wider">Catégories</h3>
            <div class="space-y-1.5 max-h-60 overflow-y-auto">
                <label class="flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 px-2 py-1.5 rounded-lg transition-colors -mx-2">
                    <input type="radio" name="category" value=""
                        {{ !request('category') ? 'checked' : '' }}
                        class="w-4 h-4 text-primary-600 focus:ring-primary-500 border-slate-300">
                    <span class="text-sm text-slate-700 font-medium">Toutes les catégories</span>
                </label>
                @foreach($categories as $category)
                <label class="flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 px-2 py-1.5 rounded-lg transition-colors -mx-2">
                    <input type="radio" name="category" value="{{ $category->slug }}"
                        {{ request('category') === $category->slug ? 'checked' : '' }}
                        class="w-4 h-4 text-primary-600 focus:ring-primary-500 border-slate-300">
                    <span class="text-sm text-slate-700 flex-1">{{ $category->name }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Prix --}}
    <div class="{{ $isMobile ? '' : 'bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100' }}">
        @unless($isMobile)<div class="h-1 bg-gradient-to-r from-primary-500/20 to-transparent rounded-t-2xl"></div>@endunless
        <div class="{{ $isMobile ? '' : 'p-5' }}">
            <h3 class="font-bold text-slate-900 mb-3 text-sm uppercase tracking-wider">Prix (F CFA)</h3>
            <div class="flex gap-2 items-center">
                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="{{ floor($priceRange->min ?? 0) }}"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                <span class="text-slate-400 text-sm">à</span>
                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="{{ ceil($priceRange->max ?? 1000) }}"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
            </div>
        </div>
    </div>

    {{-- Couleurs --}}
    @if($colors->count() > 0)
    <div class="{{ $isMobile ? '' : 'bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100' }}">
        @unless($isMobile)<div class="h-1 bg-gradient-to-r from-primary-500/20 to-transparent rounded-t-2xl"></div>@endunless
        <div class="{{ $isMobile ? '' : 'p-5' }}">
            <h3 class="font-bold text-slate-900 mb-3 text-sm uppercase tracking-wider">Couleurs</h3>
            <div class="flex flex-wrap gap-2.5">
                @foreach($colors as $color)
                <label class="cursor-pointer group/c relative" title="{{ $color->value }}">
                    <input type="radio" name="color" value="{{ $color->slug }}" class="sr-only peer"
                        {{ request('color') === $color->slug ? 'checked' : '' }}>
                    <span class="block w-9 h-9 rounded-full border-2 border-white ring-1 ring-slate-200 peer-checked:ring-2 peer-checked:ring-primary-500 peer-checked:ring-offset-2 hover:scale-110 transition-all shadow-sm"
                        style="background-color: {{ $color->color_code }}"></span>
                </label>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Boutons d'action --}}
    <div class="{{ $isMobile ? 'sticky bottom-0 bg-white pt-3 pb-1 -mx-5 px-5 border-t border-slate-100' : '' }} space-y-2">
        <button type="submit" class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-primary-500/25 hover:-translate-y-0.5 inline-flex items-center justify-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Appliquer les filtres
        </button>
        @if(request()->hasAny(['search', 'category', 'min_price', 'max_price', 'color']))
            <a href="{{ route('shop.index') }}" class="block w-full py-2.5 text-center text-sm text-slate-500 hover:text-red-600 font-medium transition-colors">
                Réinitialiser
            </a>
        @endif
    </div>
</form>
