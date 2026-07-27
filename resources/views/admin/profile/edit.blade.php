@extends('layouts.admin')

@section('title', 'Mon profil')

@section('content')
<div class="p-4 sm:p-6 space-y-5">

    {{-- Header --}}
    <div>
        <h1 class="text-xl font-bold text-gray-900">Mon profil</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">Gérez vos informations personnelles et votre sécurité</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-[13px] flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-[13px]">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Profile header --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
            <div class="flex-shrink-0">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-20 h-20 rounded-xl object-cover ring-2 ring-gray-100">
                @else
                    <div class="w-20 h-20 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @endif
            </div>
            <div class="flex-1 text-center sm:text-left">
                <h2 class="text-[16px] font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-[13px] text-gray-500 mt-0.5">{{ $user->email }}</p>
                @if($user->phone)
                    <p class="text-[12px] text-gray-400 mt-0.5">{{ $user->phone }}</p>
                @endif
                <div class="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
                    <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 text-[11px] font-semibold rounded-lg">
                        {{ ucfirst($user->role ?? 'Admin') }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 text-[11px] font-medium rounded-lg">
                        Membre depuis {{ $user->created_at->format('M Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Informations personnelles --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-[14px] font-semibold text-gray-900">Informations personnelles</h3>
                </div>
                <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="no-ajax" x-data="{ avatarPreview: null }">
                    @csrf
                    <div class="p-5 space-y-4">

                        {{-- Avatar --}}
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                            <div class="relative w-16 h-16 flex-shrink-0">
                                <img x-show="avatarPreview" :src="avatarPreview" alt="Aperçu" class="w-16 h-16 rounded-xl object-cover ring-2 ring-white shadow">
                                <div x-show="!avatarPreview">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-16 h-16 rounded-xl object-cover ring-2 ring-white shadow">
                                    @else
                                        <div class="w-16 h-16 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 text-xl font-bold shadow">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="inline-flex items-center gap-2 h-9 px-4 bg-white border border-gray-200 rounded-lg text-[13px] font-medium text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Choisir une image
                                    <input type="file" name="avatar" accept="image/*" class="hidden"
                                        @change="const f = $event.target.files[0]; avatarPreview = f ? URL.createObjectURL(f) : null">
                                </label>
                                <p class="text-[11px] text-gray-400 mt-1.5">PNG, JPG ou WEBP. Max 2 Mo.</p>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Nom complet *</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('name')<p class="text-red-500 text-[12px] mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Email *</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('email')<p class="text-red-500 text-[12px] mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Téléphone</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}"
                                placeholder="+225 XX XX XX XX XX"
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">
                        <button type="submit" class="h-9 px-5 bg-blue-600 text-white font-medium text-[13px] rounded-lg hover:bg-blue-700 transition-colors">
                            Mettre à jour le profil
                        </button>
                    </div>
                </form>
            </div>

            {{-- Mot de passe --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-[14px] font-semibold text-gray-900">Changer le mot de passe</h3>
                </div>
                <form method="POST" action="{{ route('admin.profile.password') }}">
                    @csrf
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Mot de passe actuel *</label>
                            <input type="password" name="current_password" required placeholder="••••••••"
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('current_password')<p class="text-red-500 text-[12px] mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Nouveau mot de passe *</label>
                                <input type="password" name="password" required placeholder="••••••••"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('password')<p class="text-red-500 text-[12px] mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Confirmer *</label>
                                <input type="password" name="password_confirmation" required placeholder="••••••••"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>
                    <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">
                        <button type="submit" class="h-9 px-5 bg-amber-600 text-white font-medium text-[13px] rounded-lg hover:bg-amber-700 transition-colors">
                            Changer le mot de passe
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">

            {{-- Compte --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-[13px] font-semibold text-gray-900">Compte</h3>
                </div>
                <div class="p-5 space-y-2 text-[13px]">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500">Rôle</span>
                        <span class="font-medium text-gray-900">{{ ucfirst($user->role ?? 'Admin') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-gray-500">Créé le</span>
                        <span class="font-medium text-gray-900">{{ $user->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-500">Dernière connexion</span>
                        <span class="font-medium text-gray-900">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Sécurité --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <h3 class="text-[13px] font-semibold text-gray-900">Sécurité</h3>
                </div>
                <ul class="p-5 space-y-2.5 text-[13px] text-gray-600">
                    @foreach([
                        'Mot de passe fort (8+ caractères)',
                        'Ne partagez jamais vos identifiants',
                        'Déconnexion sur appareils partagés',
                    ] as $tip)
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ $tip }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
