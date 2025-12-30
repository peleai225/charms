@extends('layouts.admin')

@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <form method="GET" class="flex gap-3">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="px-4 py-2 border border-slate-300 rounded-xl">
            <select name="role" class="px-4 py-2 border border-slate-300 rounded-xl">
                <option value="">Tous les rôles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-200 text-slate-700 font-medium rounded-xl hover:bg-slate-300">Filtrer</button>
        </form>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvel utilisateur
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Utilisateur</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Email</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Rôle</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase">Créé le</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                @endif
                                <span class="font-medium text-slate-900">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $roleColors = ['admin' => 'red', 'manager' => 'blue', 'staff' => 'green'];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $roleColors[$user->role ?? 'staff'] ?? 'slate' }}-100 text-{{ $roleColors[$user->role ?? 'staff'] ?? 'slate' }}-700">
                                {{ ucfirst($user->role ?? 'staff') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($user->is_active ?? true)
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Actif</span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-700">Inactif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">Aucun utilisateur</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection

