<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Bannières</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">
                {{ $banners->total() }} bannière(s) au total
            </p>
        </div>
        <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center justify-center h-9 px-4 text-[13px] font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
            Nouvelle bannière
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <div class="flex flex-col sm:flex-row gap-3">

            {{-- Recherche --}}
            <div class="flex-1 relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Rechercher par nom ou titre..."
                    class="w-full h-9 px-3 pr-8 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                />
                <div wire:loading.delay wire:target="search" class="absolute right-2.5 top-1/2 -translate-y-1/2">
                    <svg class="w-4 h-4 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
            </div>

            {{-- Position --}}
            <select
                wire:model.live="position"
                class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">Toutes positions</option>
                @foreach($positions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            {{-- Type --}}
            <select
                wire:model.live="type"
                class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">Tous types</option>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            {{-- Statut --}}
            <select
                wire:model.live="status"
                class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">Tous statuts</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            {{-- Reset --}}
            @if($search || $position || $type || $status)
                <button
                    wire:click="resetFilters"
                    class="h-9 px-3 text-[13px] text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    Réinitialiser
                </button>
            @endif
        </div>
    </div>

    {{-- Loading state --}}
    <div wire:loading.delay class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-[13px]">
        Chargement en cours...
    </div>

    {{-- Flash message --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-[13px]">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($banners->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Nom</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Position</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Ordre</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Période</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($banners as $banner)
                            <tr wire:key="banner-{{ $banner->id }}" class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $banner->name ?? 'Sans nom' }}</div>
                                    @if($banner->title)
                                        <div class="text-xs text-gray-500 mt-0.5">{{ Str::limit($banner->title, 50) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $positions[$banner->position] ?? $banner->position }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $types[$banner->type] ?? $banner->type }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-900 font-medium">
                                    {{ $banner->order }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    @if($banner->starts_at || $banner->ends_at)
                                        <div>{{ $banner->starts_at?->format('d/m/Y') ?? '—' }}</div>
                                        <div class="text-gray-400">au {{ $banner->ends_at?->format('d/m/Y') ?? '—' }}</div>
                                    @else
                                        <span class="text-gray-400">Permanent</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <button
                                        wire:click="toggleActive({{ $banner->id }})"
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium transition-colors {{ $banner->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                        {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.banners.edit', $banner) }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                            Modifier
                                        </a>
                                        <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette bannière ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700 text-xs font-medium">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $banners->links() }}
            </div>
        @else
            {{-- Empty state --}}
            <div class="p-12 text-center">
                <div class="text-gray-400 mb-3">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">Aucune bannière</h3>
                <p class="text-[13px] text-gray-500">Créez votre première bannière pour commencer.</p>
            </div>
        @endif
    </div>
</div>
