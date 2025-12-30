@extends('layouts.admin')

@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <!-- Informations du profil -->
    <div class="lg:col-span-2 space-y-6">
        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            @csrf
            @method('PUT')
            
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Informations personnelles</h3>
            
            <div class="space-y-4">
                <div class="flex items-center gap-4 mb-6">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover">
                    @else
                        <div class="w-20 h-20 rounded-full bg-blue-600 flex items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Photo de profil</label>
                        <input type="file" name="avatar" accept="image/*" class="text-sm">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom complet *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                        @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                        @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-slate-100">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">
                    Mettre à jour le profil
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.profile.password') }}" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            @csrf
            @method('PUT')
            
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Changer le mot de passe</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mot de passe actuel *</label>
                    <input type="password" name="current_password" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                    @error('current_password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nouveau mot de passe *</label>
                        <input type="password" name="password" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                        @error('password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Confirmer le mot de passe *</label>
                        <input type="password" name="password_confirmation" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-slate-100">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700">
                    Changer le mot de passe
                </button>
            </div>
        </form>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Informations du compte</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Rôle</dt>
                    <dd class="font-medium text-slate-900">{{ ucfirst($user->role ?? 'Admin') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Créé le</dt>
                    <dd class="font-medium text-slate-900">{{ $user->created_at->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Dernière connexion</dt>
                    <dd class="font-medium text-slate-900">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Sécurité</h3>
            <ul class="space-y-2 text-sm text-slate-600">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Utilisez un mot de passe fort
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Ne partagez jamais vos identifiants
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Déconnectez-vous sur les appareils partagés
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection

