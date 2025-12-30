@extends('layouts.admin')

@section('title', 'Bannières')
@section('page-title', 'Gestion des bannières')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-slate-600">{{ $banners->total() }} bannière(s)</p>
        </div>
        <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle bannière
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <select name="position" class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Toutes les positions</option>
                @foreach($positions as $key => $label)
                    <option value="{{ $key }}" {{ request('position') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="type" class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Tous les types</option>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actives</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactives</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors">
                Filtrer
            </button>
            @if(request()->hasAny(['position', 'type', 'status']))
                <a href="{{ route('admin.banners.index') }}" class="px-4 py-2 text-slate-600 hover:text-slate-900">Réinitialiser</a>
            @endif
        </form>
    </div>

    <!-- Liste des bannières -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($banners as $banner)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden group">
                <!-- Image -->
                <div class="relative aspect-[16/9] bg-slate-100">
                    @if($banner->image)
                        <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                    
                    <!-- Overlay avec actions -->
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                        <a href="{{ route('admin.banners.edit', $banner) }}" class="p-2 bg-white rounded-lg text-slate-700 hover:text-blue-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Supprimer cette bannière ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 bg-white rounded-lg text-slate-700 hover:text-red-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Badge statut -->
                    <div class="absolute top-2 right-2">
                        @if($banner->is_active)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">Inactive</span>
                        @endif
                    </div>
                </div>

                <!-- Infos -->
                <div class="p-4">
                    <h3 class="font-semibold text-slate-900 truncate">{{ $banner->title ?? 'Sans titre' }}</h3>
                    @if($banner->subtitle)
                        <p class="text-sm text-slate-500 truncate">{{ $banner->subtitle }}</p>
                    @endif
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-xs px-2 py-1 bg-slate-100 text-slate-600 rounded-lg">{{ $positions[$banner->position] ?? $banner->position }}</span>
                        <span class="text-xs text-slate-500">Ordre: {{ $banner->order }}</span>
                    </div>
                    @if($banner->starts_at || $banner->ends_at)
                        <div class="text-xs text-slate-500 mt-2">
                            @if($banner->starts_at)
                                Du {{ $banner->starts_at->format('d/m/Y') }}
                            @endif
                            @if($banner->ends_at)
                                au {{ $banner->ends_at->format('d/m/Y') }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-slate-500 mb-4">Aucune bannière</p>
                <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                    Créer une bannière
                </a>
            </div>
        @endforelse
    </div>

    {{ $banners->links() }}
</div>
@endsection

