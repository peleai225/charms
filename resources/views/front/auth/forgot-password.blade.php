@extends('layouts.front')

@section('title', 'Mot de passe oublié')

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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-extrabold text-white mb-4">Réinitialisation</h2>
            <p class="text-white/70 text-lg max-w-sm">Pas de panique, nous allons vous aider à récupérer votre compte.</p>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12 bg-white">
        <div class="w-full max-w-md">
            <div class="text-center mb-8 lg:text-left">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-primary-100 mb-4 lg:hidden">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900">Mot de passe oublié ?</h1>
                <p class="text-slate-500 mt-2">Entrez votre email, nous vous enverrons un lien de réinitialisation.</p>
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

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Adresse email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full px-4 py-3.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                        placeholder="votre@email.com" required autofocus>
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">
                    Envoyer le lien de réinitialisation
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
