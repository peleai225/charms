@extends('layouts.admin')

@section('title', 'Utilisateurs')

@section('content')
<div class="p-4 sm:p-6 space-y-5" x-data>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Utilisateurs</h1>
            <p class="text-[13px] text-gray-500 mt-0.5">{{ $users->total() }} utilisateur(s)</p>
        </div>
        <button type="button"
                @click="$dispatch('open-modal', 'user-create')"
                class="h-9 px-4 inline-flex items-center gap-2 text-[13px] font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvel utilisateur
        </button>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
                       class="pl-9 pr-4 h-9 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all w-52">
            </div>
            <select name="role" class="h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Tous les rôles</option>
                <option value="admin"   {{ request('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
                <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="staff"   {{ request('role') === 'staff'   ? 'selected' : '' }}>Staff</option>
            </select>
            <button type="submit" class="h-9 px-4 bg-gray-800 text-white font-medium text-[13px] rounded-lg hover:bg-gray-700 transition-colors">Filtrer</button>
            @if(request()->hasAny(['search', 'role']))
                <a href="{{ route('admin.users.index') }}" class="h-9 px-3 inline-flex items-center text-[13px] text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Effacer
                </a>
            @endif
        </form>
    </div>

    {{-- Table Desktop --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hidden md:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Utilisateur</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Rôle</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Créé le</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                    <tr class="group hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="w-9 h-9 rounded-lg object-cover">
                                @else
                                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700 text-[11px] font-bold flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                @endif
                                <span class="text-[13px] font-medium text-gray-900 group-hover:text-blue-700 transition-colors">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[13px] text-gray-500">{{ $user->email }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full
                                @switch($user->role ?? 'staff')
                                    @case('admin') bg-red-50 text-red-700 @break
                                    @case('manager') bg-blue-50 text-blue-700 @break
                                    @case('staff') bg-green-50 text-green-700 @break
                                    @default bg-gray-100 text-gray-500
                                @endswitch">
                                <span class="w-1.5 h-1.5 rounded-full
                                    @switch($user->role ?? 'staff')
                                        @case('admin') bg-red-500 @break
                                        @case('manager') bg-blue-500 @break
                                        @case('staff') bg-green-500 @break
                                        @default bg-gray-400
                                    @endswitch"></span>
                                {{ ucfirst($user->role ?? 'staff') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($user->is_active ?? true)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-50 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Actif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-[12px] text-gray-400">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="h-7 w-7 inline-flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-all"
                                   title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-7 w-7 inline-flex items-center justify-center text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition-all" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <p class="text-[13px] text-gray-400 mb-1">Aucun utilisateur</p>
                            <p class="text-[12px] text-gray-300">Ajoutez des utilisateurs pour gérer votre boutique</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-3">
        @forelse($users as $user)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="w-11 h-11 rounded-lg object-cover flex-shrink-0">
                @else
                    <div class="w-11 h-11 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700 font-bold flex-shrink-0">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-medium text-gray-900 truncate">{{ $user->name }}</p>
                    <p class="text-[12px] text-gray-500 truncate">{{ $user->email }}</p>
                </div>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full
                    @switch($user->role ?? 'staff')
                        @case('admin') bg-red-50 text-red-700 @break
                        @case('manager') bg-blue-50 text-blue-700 @break
                        @default bg-green-50 text-green-700
                    @endswitch">
                    {{ ucfirst($user->role ?? 'staff') }}
                </span>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                <span class="text-[11px] text-gray-400">Créé le {{ $user->created_at->format('d/m/Y') }}</span>
                <a href="{{ route('admin.users.edit', $user) }}" class="h-8 px-3 inline-flex items-center text-[13px] text-blue-600 hover:bg-blue-50 rounded transition-colors">
                    Modifier →
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
            <p class="text-[13px] text-gray-400">Aucun utilisateur</p>
        </div>
        @endforelse
        @if($users->hasPages())
        <div class="mt-4">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- Modal création utilisateur --}}
    <x-admin.modal id="user-create" title="Nouvel utilisateur" maxWidth="max-w-lg" :open="request('open_modal') === 'create' || $errors->any()">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4" data-ajax>
            @csrf

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="modal_name" class="block text-[13px] font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" name="name" id="modal_name" value="{{ old('name') }}" required
                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('name')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="modal_email" class="block text-[13px] font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" id="modal_email" value="{{ old('email') }}" required
                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('email')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="modal_password" class="block text-[13px] font-medium text-gray-700 mb-1">Mot de passe *</label>
                    <input type="password" name="password" id="modal_password" required
                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('password')<p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="modal_password_confirmation" class="block text-[13px] font-medium text-gray-700 mb-1">Confirmer *</label>
                    <input type="password" name="password_confirmation" id="modal_password_confirmation" required
                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="modal_role" class="block text-[13px] font-medium text-gray-700 mb-1">Rôle *</label>
                    <select name="role" id="modal_role" required
                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="staff"   {{ old('role') === 'staff'   ? 'selected' : '' }}>Staff</option>
                        <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="admin"   {{ old('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="flex items-center pt-7">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-[13px] text-gray-700">Compte actif</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="h-9 px-5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[13px] rounded-lg transition-colors">
                    Créer l'utilisateur
                </button>
                <button type="button"
                        @click="$dispatch('close-modal', 'user-create')"
                        class="h-9 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-[13px] rounded-lg transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </x-admin.modal>

</div>
@endsection
