@extends('layouts.admin')

@section('title', 'Bannières')
@section('page-title', 'Gestion des bannières')

@section('content')
@php
$positionColors = [
    'announcement_bar' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-300', 'dot' => 'bg-amber-500', 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
    'popup_center'     => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'border' => 'border-indigo-300', 'dot' => 'bg-indigo-500', 'icon' => 'M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z'],
    'home_hero'        => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'border' => 'border-blue-300',   'dot' => 'bg-blue-500',   'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    'home_middle'      => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'border' => 'border-green-300',  'dot' => 'bg-green-500',  'icon' => 'M4 6h16M4 12h16M4 18h16'],
    'home_bottom'      => ['bg' => 'bg-teal-100',   'text' => 'text-teal-700',   'border' => 'border-teal-300',   'dot' => 'bg-teal-500',   'icon' => 'M19 14l-7 7m0 0l-7-7m7 7V3'],
    'category_top'     => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'border' => 'border-purple-300', 'dot' => 'bg-purple-500', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
    'product_sidebar'  => ['bg' => 'bg-rose-100',   'text' => 'text-rose-700',   'border' => 'border-rose-300',   'dot' => 'bg-rose-500',   'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2'],
    'cart_bottom'      => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'border' => 'border-orange-300', 'dot' => 'bg-orange-500', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
    'checkout_top'     => ['bg' => 'bg-cyan-100',   'text' => 'text-cyan-700',   'border' => 'border-cyan-300',   'dot' => 'bg-cyan-500',   'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
];

// Grouper les bannières par position
$grouped = $banners->groupBy('position');
@endphp

<div class="space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-slate-500 text-sm">{{ $banners->total() }} bannière(s) au total</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.banners.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors shadow-sm shadow-blue-600/20 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvelle bannière
            </a>
        </div>
    </div>

    {{-- ===== CARTE VISUELLE DES POSITIONS ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Carte des positions</h3>
        <div class="flex flex-col gap-1.5 max-w-sm mx-auto">
            {{-- Announcement bar --}}
            <div class="rounded-lg px-3 py-2 text-xs font-semibold text-center {{ ($grouped->has('announcement_bar') && $grouped['announcement_bar']->where('is_active',true)->count()) ? 'bg-amber-500 text-white' : 'bg-amber-100 text-amber-600 border border-dashed border-amber-300' }}">
                Barre d'annonce (haut)
                <span class="ml-1 opacity-70">{{ $grouped->get('announcement_bar', collect())->count() }}</span>
            </div>
            {{-- Hero --}}
            <div class="rounded-lg px-3 py-8 text-xs font-semibold text-center {{ ($grouped->has('home_hero') && $grouped['home_hero']->where('is_active',true)->count()) ? 'bg-blue-500 text-white' : 'bg-blue-100 text-blue-600 border border-dashed border-blue-300' }}">
                Hero / Slider accueil
                <span class="ml-1 opacity-70">{{ $grouped->get('home_hero', collect())->count() }}</span>
            </div>
            {{-- Middle --}}
            <div class="rounded-lg px-3 py-4 text-xs font-semibold text-center {{ ($grouped->has('home_middle') && $grouped['home_middle']->where('is_active',true)->count()) ? 'bg-green-500 text-white' : 'bg-green-100 text-green-600 border border-dashed border-green-300' }}">
                Accueil milieu
                <span class="ml-1 opacity-70">{{ $grouped->get('home_middle', collect())->count() }}</span>
            </div>
            {{-- Bottom --}}
            <div class="rounded-lg px-3 py-3 text-xs font-semibold text-center {{ ($grouped->has('home_bottom') && $grouped['home_bottom']->where('is_active',true)->count()) ? 'bg-teal-500 text-white' : 'bg-teal-100 text-teal-600 border border-dashed border-teal-300' }}">
                Accueil bas
                <span class="ml-1 opacity-70">{{ $grouped->get('home_bottom', collect())->count() }}</span>
            </div>
            {{-- Other positions in a row --}}
            <div class="grid grid-cols-3 gap-1.5">
                @foreach(['category_top' => 'Catégorie', 'product_sidebar' => 'Sidebar produit', 'cart_bottom' => 'Panier', 'checkout_top' => 'Checkout', 'popup_center' => 'Popup'] as $pos => $label)
                @php $c = $positionColors[$pos]; @endphp
                <div class="rounded-lg px-2 py-2 text-[10px] font-semibold text-center {{ ($grouped->has($pos) && $grouped[$pos]->where('is_active',true)->count()) ? $c['dot'] . ' text-white' : $c['bg'] . ' ' . $c['text'] . ' border border-dashed ' . $c['border'] }}">
                    {{ $label }}
                    <span class="block opacity-70">{{ $grouped->get($pos, collect())->count() }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <p class="text-center text-xs text-slate-400 mt-3">Coloré = au moins une bannière active · Hachuré = aucune</p>
    </div>

    {{-- ===== FILTRES ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <select name="position" onchange="this.form.submit()" class="px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Toutes les positions</option>
                @foreach($positions as $key => $label)
                    <option value="{{ $key }}" {{ request('position') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Tous les statuts</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Actives</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactives</option>
            </select>
            @if(request()->hasAny(['position', 'status']))
                <a href="{{ route('admin.banners.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-900 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    {{-- ===== BANNIÈRES GROUPÉES PAR POSITION ===== --}}
    @if($banners->count() > 0)
    @foreach($positions as $posKey => $posLabel)
    @php
        $posBanners = $banners->where('position', $posKey);
        $c = $positionColors[$posKey] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-300', 'dot' => 'bg-slate-400', 'icon' => 'M4 6h16M4 12h16M4 18h16'];
    @endphp
    @if($posBanners->count() > 0)
    <div class="space-y-3">
        {{-- Titre de groupe --}}
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl {{ $c['bg'] }} {{ $c['border'] }} border flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 {{ $c['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $c['icon'] }}"/>
                </svg>
            </div>
            <h3 class="font-semibold text-slate-800">{{ $posLabel }}</h3>
            <span class="text-xs {{ $c['bg'] }} {{ $c['text'] }} px-2 py-0.5 rounded-full font-medium">
                {{ $posBanners->count() }} bannière(s) · {{ $posBanners->where('is_active', true)->count() }} active(s)
            </span>
        </div>

        {{-- Grille --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($posBanners->sortBy('order') as $banner)
            <div class="bg-white rounded-2xl border {{ $banner->is_active ? 'border-slate-200 shadow-sm' : 'border-slate-200 opacity-60' }} overflow-hidden group"
                 x-data="{ active: {{ $banner->is_active ? 'true' : 'false' }}, saving: false }">

                {{-- Image --}}
                <div class="relative aspect-[16/7] bg-slate-100 overflow-hidden">
                    @if($banner->image)
                        <img src="{{ asset('storage/' . $banner->image) }}"
                             alt="{{ $banner->title }}"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 gap-2"
                             style="{{ $banner->background_color ? 'background-color:' . $banner->background_color : '' }}">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            @if($banner->title)
                                <span class="text-xs font-semibold {{ $banner->text_color ? '' : 'text-slate-500' }}"
                                      style="{{ $banner->text_color ? 'color:' . $banner->text_color : '' }}">{{ $banner->title }}</span>
                            @endif
                        </div>
                    @endif

                    {{-- Overlay actions --}}
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <a href="{{ route('admin.banners.edit', $banner) }}"
                           class="p-2 bg-white rounded-xl text-slate-700 hover:text-blue-600 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @if($banner->link)
                        <a href="{{ $banner->link }}" target="_blank"
                           class="p-2 bg-white rounded-xl text-slate-700 hover:text-green-600 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        @endif
                        <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST"
                              onsubmit="return confirm('Supprimer cette bannière ?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 bg-white rounded-xl text-slate-700 hover:text-red-600 transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>

                    {{-- Ordre --}}
                    <div class="absolute top-2 left-2">
                        <span class="px-2 py-0.5 bg-black/50 text-white text-[10px] font-bold rounded-lg">#{{ $banner->order }}</span>
                    </div>
                </div>

                {{-- Infos + toggle --}}
                <div class="p-3">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="min-w-0">
                            <h4 class="font-semibold text-slate-900 text-sm truncate">{{ $banner->title ?? 'Sans titre' }}</h4>
                            @if($banner->subtitle)
                                <p class="text-xs text-slate-400 truncate">{{ $banner->subtitle }}</p>
                            @endif
                        </div>
                        {{-- Toggle actif / inactif --}}
                        <button type="button"
                            :disabled="saving"
                            @click="
                                saving=true;
                                fetch('{{ route('admin.banners.toggle', $banner) }}', {
                                    method:'PATCH',
                                    headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
                                }).then(r=>r.json()).then(d=>{ active=d.is_active; }).finally(()=>{ saving=false; });
                            "
                            :class="active ? 'bg-green-500 hover:bg-green-600' : 'bg-slate-300 hover:bg-slate-400'"
                            :title="active ? 'Cliquer pour désactiver' : 'Cliquer pour activer'"
                            class="relative w-10 h-5 rounded-full flex-shrink-0 transition-colors duration-200">
                            <span :class="active ? 'translate-x-5' : 'translate-x-0.5'"
                                  class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"></span>
                        </button>
                    </div>

                    {{-- Dates --}}
                    @if($banner->starts_at || $banner->ends_at)
                    <div class="flex items-center gap-1 text-[10px] text-slate-400 mt-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @if($banner->starts_at) Du {{ $banner->starts_at->format('d/m/Y') }} @endif
                        @if($banner->ends_at) au {{ $banner->ends_at->format('d/m/Y') }} @endif
                    </div>
                    @endif

                    {{-- Lien --}}
                    @if($banner->link)
                    <div class="text-[10px] text-slate-400 truncate mt-1">
                        <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        {{ $banner->link }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Ajouter dans cette position --}}
            <a href="{{ route('admin.banners.create') }}?position={{ $posKey }}"
               class="border-2 border-dashed border-slate-200 hover:border-blue-400 rounded-2xl flex flex-col items-center justify-center gap-2 py-8 text-slate-400 hover:text-blue-500 transition-colors min-h-[120px]">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="text-xs font-medium">Ajouter ici</span>
            </a>
        </div>
    </div>
    @endif
    @endforeach
    @else
    {{-- Empty state --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-16 text-center">
        <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-slate-500 mb-2 font-medium">Aucune bannière</p>
        <p class="text-slate-400 text-sm mb-5">Créez votre première bannière pour personnaliser l'apparence de votre boutique.</p>
        <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Créer une bannière
        </a>
    </div>
    @endif

    {{ $banners->links() }}
</div>
@endsection
