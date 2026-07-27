@extends('layouts.admin')

@section('title', 'Nouvel utilisateur')
@section('page-title', 'Nouvel utilisateur')

@section('content')

@if ($errors->any())
<div class="mb-5 flex gap-3 bg-orange-50 border border-orange-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
    <div>
        <p class="text-[13px] font-semibold text-orange-800">Erreurs de validation</p>
        <ul class="mt-1 space-y-0.5">
            @foreach ($errors->all() as $error)
            <li class="text-[12px] text-orange-700">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="max-w-2xl">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.users.index') }}" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-[15px] font-bold text-gray-900">Nouvel utilisateur admin</h1>
            <p class="text-[12px] text-gray-400">Créer un compte avec accès backoffice</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Informations du compte</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('name') border-red-300 @enderror">
                    @error('name') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('email') border-red-300 @enderror">
                    @error('email') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Mot de passe <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required autocomplete="new-password"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('password') border-red-300 @enderror">
                    @error('password') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Confirmer le mot de passe <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                        class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Rôle <span class="text-red-500">*</span></label>
                <select name="role" required
                    class="w-full h-9 px-3 text-[13px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 @error('role') border-red-300 @enderror">
                    <option value="">— Choisir un rôle —</option>
                    <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Note sur les rôles --}}
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-2">
            <p class="text-[12px] font-semibold text-gray-700">Permissions par rôle</p>
            <div class="space-y-1.5">
                <div class="flex items-start gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-700 ring-1 ring-red-100 flex-shrink-0 mt-0.5">Admin</span>
                    <span class="text-[12px] text-gray-500">Accès complet — gestion des utilisateurs, paramètres globaux, toutes les données.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-100 flex-shrink-0 mt-0.5">Manager</span>
                    <span class="text-[12px] text-gray-500">Gestion produits, commandes, clients et rapports. Ne peut pas gérer les utilisateurs.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 flex-shrink-0 mt-0.5">Staff</span>
                    <span class="text-[12px] text-gray-500">Consultation commandes et produits. Accès lecture seule sur la majorité des sections.</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="h-9 px-6 bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition-colors">
                Créer l'utilisateur
            </button>
            <a href="{{ route('admin.users.index') }}"
                class="h-9 px-4 inline-flex items-center border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Annuler
            </a>
        </div>
    </form>
</div>

@endsection
