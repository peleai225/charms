<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Utilisateurs</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">
                {{ $users->total() }} utilisateur(s) au total
            </p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center h-9 px-4 text-[13px] font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
            Nouvel utilisateur
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
                    placeholder="Rechercher par nom ou email..."
                    class="w-full h-9 px-3 pr-8 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                />
                <div wire:loading.delay wire:target="search" class="absolute right-2.5 top-1/2 -translate-y-1/2">
                    <svg class="w-4 h-4 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
            </div>

            {{-- Rôle --}}
            <select
                wire:model.live="role"
                class="h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">Tous les rôles</option>
                <option value="admin">Administrateur</option>
                <option value="manager">Manager</option>
                <option value="staff">Staff</option>
            </select>

            {{-- Reset --}}
            @if($search || $role)
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
        @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Utilisateur</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Rôle</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Créé le</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-medium">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                            @if($user->id === auth()->id())
                                                <div class="text-xs text-blue-600">Vous</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $user->email }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                        @if($user->role === 'admin') bg-purple-100 text-purple-700
                                        @elseif($user->role === 'manager') bg-blue-100 text-blue-700
                                        @elseif($user->role === 'staff') bg-gray-100 text-gray-700
                                        @else bg-gray-100 text-gray-600
                                        @endif">
                                        @if($user->role === 'admin') Administrateur
                                        @elseif($user->role === 'manager') Manager
                                        @elseif($user->role === 'staff') Staff
                                        @else {{ $user->role }}
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-gray-600 hover:text-gray-700 text-xs font-medium">
                                            Voir
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                            Modifier
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-700 text-xs font-medium">
                                                    Supprimer
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $users->links() }}
            </div>
        @else
            {{-- Empty state --}}
            <div class="p-12 text-center">
                <div class="text-gray-400 mb-3">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">Aucun utilisateur</h3>
                <p class="text-[13px] text-gray-500">Créez votre premier utilisateur pour commencer.</p>
            </div>
        @endif
    </div>
</div>
