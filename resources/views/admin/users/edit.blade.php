@extends('layouts.admin')

@section('title', 'Modifier l\'utilisateur')
@section('page-title', 'Modifier l\'utilisateur')

@section('content')
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-2xl">
    @csrf
    @method('PUT')

    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nom *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nouveau mot de passe</label>
                <input type="password" name="password" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Laisser vide pour ne pas changer">
                @error('password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Laisser vide pour ne pas changer">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Rôle *</label>
                <select name="role" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                    <option value="customer" {{ old('role', $user->role) === 'customer' ? 'selected' : '' }}>Client</option>
                    <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center pt-6">
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                    <span class="text-slate-700">Compte actif</span>
                </label>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">
                Mettre à jour l'utilisateur
            </button>
        </div>
    </div>
</form>
@endsection

