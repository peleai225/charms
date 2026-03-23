@extends('layouts.front')

@section('title', 'Réinitialiser le mot de passe')

@section('content')
<div class="min-h-[85vh] flex">
    <!-- Left decorative panel -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-600 via-primary-700 to-slate-900 relative overflow-hidden items-center justify-center p-12">
        <div class="absolute -top-20 -left-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-violet-500/10 rounded-full blur-3xl"></div>
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
        <div class="relative text-center">
            <div class="w-20 h-20 bg-white/15 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-8 ring-2 ring-white/20">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-extrabold text-white mb-4">Nouveau départ</h2>
            <p class="text-white/70 text-lg max-w-sm">Choisissez un mot de passe sécurisé pour protéger votre compte.</p>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12 bg-white">
        <div class="w-full max-w-md">
            <div class="text-center mb-8 lg:text-left">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-primary-100 mb-4 lg:hidden">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900">Réinitialiser le mot de passe</h1>
                <p class="text-slate-500 mt-2">Entrez votre nouveau mot de passe ci-dessous.</p>
            </div>

            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-600 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
                    <ul class="list-disc list-inside text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="email_display" class="block text-sm font-medium text-slate-700 mb-1.5">Adresse email</label>
                    <input type="email" id="email_display" value="{{ $email }}" readonly
                        class="w-full px-4 py-3.5 border border-slate-300 rounded-xl bg-slate-50 text-slate-600 cursor-not-allowed">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Nouveau mot de passe</label>
                    <input type="password" name="password" id="password"
                        class="w-full px-4 py-3.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                        placeholder="Minimum 8 caractères" required autofocus>
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-slate-500">
                        Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.
                    </p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="w-full px-4 py-3.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                        placeholder="Répétez le mot de passe" required>
                    @error('password_confirmation')
                        <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">
                    Réinitialiser le mot de passe
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium inline-flex items-center gap-1">
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
