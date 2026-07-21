@extends('layouts.front')

@section('title', 'Mot de passe oublié')

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-4">
    <div class="max-w-sm mx-auto w-full mt-12 mb-20">

        {{-- Icône + Titre --}}
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Mot de passe oublié ?</h1>
            <p class="mt-2 text-sm text-slate-500 max-w-xs mx-auto">
                Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">

            @if (session('status'))
            <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl">
                <div class="flex items-center gap-2 text-green-700">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium">{{ session('status') }}</p>
                </div>
            </div>
            @endif

            @if (session('error'))
            <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4 no-ajax">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Adresse email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full px-4 py-3 bg-slate-50 border @error('email') border-red-400 bg-red-50 @else border-slate-200 @enderror rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                        placeholder="votre@email.com" required autofocus>
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                    Envoyer le lien de réinitialisation
                </button>
            </form>

            <div class="mt-5 text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-blue-600 transition-colors">
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
