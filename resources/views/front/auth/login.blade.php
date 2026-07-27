@extends('layouts.front')

@section('title', 'Connexion')
@section('hide_site_chrome', '1')

@php $siteName = \App\Models\Setting::get('site_name', config('app.name')); @endphp

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-4" x-data="{ showPass: false }">
    <div class="max-w-sm mx-auto w-full mt-12 mb-20">

        {{-- Logo + Titre --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 text-slate-900 mb-4">
                <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <span class="font-bold text-xl">{{ $siteName }}</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900">Connexion</h1>
            <p class="mt-1 text-sm text-slate-500">Accédez à votre espace client</p>
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

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4 no-ajax">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Adresse email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full px-4 py-3 bg-slate-50 border @error('email') border-red-400 bg-red-50 @else border-slate-200 @enderror rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm"
                        placeholder="votre@email.com" required autofocus>
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="text-sm font-medium text-slate-700">Mot de passe</label>
                        <a href="{{ route('password.request') }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                            Mot de passe oublié ?
                        </a>
                    </div>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password" id="password"
                            class="w-full pl-4 pr-12 py-3 bg-slate-50 border @error('password') border-red-400 bg-red-50 @else border-slate-200 @enderror rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm"
                            placeholder="••••••••" required>
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
                </div>

                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="remember" id="remember"
                        class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-slate-600 select-none">Se souvenir de moi</span>
                </label>

                <button type="submit"
                    class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition-colors">
                    Se connecter
                </button>
            </form>

            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-3 bg-white text-xs text-slate-400 uppercase tracking-wider">ou</span>
                </div>
            </div>

            <p class="text-center text-sm text-slate-600">
                Pas encore de compte ?
                <a href="{{ route('register') }}" class="text-primary-600 font-semibold hover:text-primary-700 transition-colors">
                    Créer un compte
                </a>
            </p>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            En vous connectant vous acceptez nos
            <a href="{{ route('legal', 'conditions-generales') }}" class="underline underline-offset-2 hover:text-primary-600">CGV</a>
            et notre
            <a href="{{ route('legal', 'politique-de-confidentialite') }}" class="underline underline-offset-2 hover:text-primary-600">politique de confidentialité</a>
        </p>
    </div>
</div>
@endsection
