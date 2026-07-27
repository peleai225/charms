@extends('layouts.front')

@section('title', 'Réinitialiser le mot de passe')

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-4" x-data="{ showPass: false, showPassConfirm: false }">
    <div class="max-w-sm mx-auto w-full mt-12 mb-20">

        {{-- Icône + Titre --}}
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Nouveau mot de passe</h1>
            <p class="mt-1 text-sm text-slate-500">Choisissez un mot de passe sécurisé</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">

            @if (session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
                {{ session('status') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4 no-ajax">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                {{-- Email (lecture seule) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Adresse email</label>
                    <input type="email" value="{{ $email }}" readonly
                        class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 text-sm cursor-not-allowed">
                </div>

                {{-- Nouveau mot de passe --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Nouveau mot de passe <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password" id="password"
                            class="w-full pl-4 pr-12 py-3 bg-slate-50 border @error('password') border-red-400 bg-red-50 @else border-slate-200 @enderror rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm"
                            placeholder="Minimum 8 caractères" required autofocus>
                        <button type="button" @click="showPass = !showPass"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-400">8 caractères minimum, une majuscule, une minuscule et un chiffre.</p>
                </div>

                {{-- Confirmer mot de passe --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Confirmer le mot de passe <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPassConfirm ? 'text' : 'password'" name="password_confirmation" id="password_confirmation"
                            class="w-full pl-4 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm"
                            placeholder="Répétez le mot de passe" required>
                        <button type="button" @click="showPassConfirm = !showPassConfirm"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <svg x-show="!showPassConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
                    Réinitialiser le mot de passe
                </button>
            </form>

            <div class="mt-5 text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-primary-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
