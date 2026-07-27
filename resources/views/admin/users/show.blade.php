@extends('layouts.admin')

@section('title', $user->name)
@section('page-title', 'Profil utilisateur')

@section('content')

@if (session('success'))
<div class="mb-5 flex gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <p class="text-[13px] text-green-800">{{ session('success') }}</p>
</div>
@endif

<div class="max-w-3xl">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-[15px] font-bold text-gray-900">{{ $user->name }}</h1>
                    <p class="text-[12px] text-gray-400">{{ $user->email }}</p>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.users.edit', $user) }}"
            class="h-9 px-4 inline-flex items-center gap-2 bg-orange-600 text-white text-[13px] font-semibold rounded-lg hover:bg-orange-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            Modifier
        </a>
    </div>

    <div class="space-y-5">
        {{-- Infos générales --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Informations du compte</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Nom</p>
                    <p class="text-[13px] text-gray-900">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Email</p>
                    <p class="text-[13px] text-gray-900">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Rôle</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                        @switch($user->role ?? 'staff')
                            @case('admin') bg-red-50 text-red-700 ring-1 ring-red-100 @break
                            @case('manager') bg-blue-50 text-blue-700 ring-1 ring-blue-100 @break
                            @case('staff') bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 @break
                            @default bg-gray-50 text-gray-600 ring-1 ring-gray-200
                        @endswitch">
                        {{ ucfirst($user->role ?? 'staff') }}
                    </span>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Statut</p>
                    @if($user->is_active ?? true)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-green-50 text-green-700 ring-1 ring-green-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            Actif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-50 text-gray-500 ring-1 ring-gray-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Inactif
                        </span>
                    @endif
                </div>
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Créé le</p>
                    <p class="text-[13px] text-gray-900">{{ $user->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wide mb-0.5">Dernière modification</p>
                    <p class="text-[13px] text-gray-900">{{ $user->updated_at->format('d/m/Y à H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        @if($user->id !== auth()->id())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5" x-data="{ confirm: false }">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Zone de danger</h2>
            <p class="text-[12px] text-gray-500 mb-3">La suppression de l'utilisateur est définitive.</p>
            <template x-if="!confirm">
                <button type="button" @click="confirm = true"
                    class="h-9 px-4 border border-red-200 text-[13px] font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                    Supprimer l'utilisateur
                </button>
            </template>
            <template x-if="confirm">
                <div class="flex items-center gap-3">
                    <p class="text-[12px] font-semibold text-red-700">Confirmer la suppression ?</p>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="h-9 px-4 bg-red-600 text-white text-[13px] font-semibold rounded-lg hover:bg-red-700 transition-colors">
                            Confirmer
                        </button>
                    </form>
                    <button type="button" @click="confirm = false"
                        class="h-9 px-4 border border-gray-200 text-[13px] font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Annuler
                    </button>
                </div>
            </template>
        </div>
        @endif
    </div>
</div>

@endsection
