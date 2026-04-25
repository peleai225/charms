@extends('layouts.front')

@section('title', 'Créer un compte')
@section('hide_site_chrome', '1')

@php $siteName = \App\Models\Setting::get('site_name', config('app.name', '{{ $siteName }}')); @endphp

@section('content')
<div class="min-h-screen flex" x-data="{
    showPass: false,
    showPassConfirm: false,
    password: '',
    strength: 0,
    get strengthLabel() {
        if (!this.password) return '';
        if (this.strength < 2) return 'Faible';
        if (this.strength < 4) return 'Moyen';
        return 'Fort';
    },
    get strengthColor() {
        if (this.strength < 2) return 'bg-red-500';
        if (this.strength < 4) return 'bg-amber-500';
        return 'bg-emerald-500';
    },
    calcStrength(v) {
        this.password = v;
        let s = 0;
        if (v.length >= 8) s++;
        if (/[A-Z]/.test(v)) s++;
        if (/[0-9]/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        if (v.length >= 12) s++;
        this.strength = s;
    }
}">

    {{-- Panneau gauche : formulaire --}}
    <div class="flex-1 flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-16 bg-white order-2 lg:order-1">
        <div class="max-w-lg w-full mx-auto">

            {{-- Logo mobile --}}
            <div class="lg:hidden mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-slate-900 font-bold text-lg">
                    <div class="w-9 h-9 bg-primary-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    {{ $siteName }}
                </a>
            </div>

            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-900 mb-2">Créer un compte</h1>
                <p class="text-slate-500">
                    Déjà inscrit ?
                    <a href="{{ route('login') }}" class="text-primary-600 font-semibold hover:text-primary-700 transition-colors">Connectez-vous →</a>
                </p>
            </div>

            @if (session('error'))
            <div class="mb-5 flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-red-700 text-sm">{{ session('error') }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="space-y-4 no-ajax">
                @csrf

                {{-- Prénom / Nom --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="first_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Prénom <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                            class="w-full px-4 py-3 bg-slate-50 border @error('first_name') border-red-400 bg-red-50 @else border-slate-200 @enderror rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 focus:bg-white transition-all text-sm"
                            placeholder="Jean" required>
                        @error('first_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nom <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                            class="w-full px-4 py-3 bg-slate-50 border @error('last_name') border-red-400 bg-red-50 @else border-slate-200 @enderror rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 focus:bg-white transition-all text-sm"
                            placeholder="Dupont" required>
                        @error('last_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Adresse email <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border @error('email') border-red-400 bg-red-50 @else border-slate-200 @enderror rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 focus:bg-white transition-all text-sm"
                            placeholder="votre@email.com" required>
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Téléphone --}}
                <div>
                    <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Téléphone <span class="text-slate-400 font-normal text-xs">(optionnel)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 focus:bg-white transition-all text-sm"
                            placeholder="+225 07 07 07 07 07">
                    </div>
                </div>

                {{-- Mot de passe --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Mot de passe <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" name="password" id="password"
                            @input="calcStrength($event.target.value)"
                            class="w-full pl-11 pr-12 py-3 bg-slate-50 border @error('password') border-red-400 bg-red-50 @else border-slate-200 @enderror rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 focus:bg-white transition-all text-sm"
                            placeholder="Minimum 8 caractères" required>
                        <button type="button" @click="showPass = !showPass"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    {{-- Indicateur de force --}}
                    <div x-show="password.length > 0" class="mt-2 space-y-1">
                        <div class="flex gap-1">
                            <template x-for="i in 5">
                                <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                     :class="i <= strength ? strengthColor : 'bg-slate-200'"></div>
                            </template>
                        </div>
                        <p class="text-xs" :class="{
                            'text-red-500': strength < 2,
                            'text-amber-600': strength >= 2 && strength < 4,
                            'text-emerald-600': strength >= 4
                        }">
                            Sécurité : <span x-text="strengthLabel" class="font-semibold"></span>
                        </p>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirmer mot de passe --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">Confirmer le mot de passe <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <input :type="showPassConfirm ? 'text' : 'password'" name="password_confirmation" id="password_confirmation"
                            class="w-full pl-11 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 focus:bg-white transition-all text-sm"
                            placeholder="Même mot de passe" required>
                        <button type="button" @click="showPassConfirm = !showPassConfirm"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <svg x-show="!showPassConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPassConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Checkboxes --}}
                <div class="space-y-3 pt-1">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="relative flex-shrink-0 mt-0.5">
                            <input type="checkbox" name="newsletter" id="newsletter" value="1" class="sr-only peer">
                            <div class="w-4 h-4 bg-slate-100 border-2 border-slate-300 rounded peer-checked:bg-primary-600 peer-checked:border-primary-600 transition-all group-hover:border-primary-400"></div>
                            <svg class="absolute inset-0 w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity p-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm text-slate-600 select-none leading-relaxed">Recevoir les offres promotionnelles et nouveautés par email</span>
                    </label>

                    <div>
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <div class="relative flex-shrink-0 mt-0.5">
                                <input type="checkbox" name="terms" id="terms" value="1" required class="sr-only peer">
                                <div class="w-4 h-4 bg-slate-100 border-2 border-slate-300 rounded peer-checked:bg-primary-600 peer-checked:border-primary-600 transition-all group-hover:border-primary-400"></div>
                                <svg class="absolute inset-0 w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity p-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-sm text-slate-600 select-none leading-relaxed">
                                J'accepte les <a href="{{ route('legal', 'conditions-generales') }}" target="_blank" class="text-primary-600 hover:underline font-medium">conditions générales</a> et la <a href="{{ route('legal', 'politique-de-confidentialite') }}" target="_blank" class="text-primary-600 hover:underline font-medium">politique de confidentialité</a> <span class="text-red-500">*</span>
                            </span>
                        </label>
                        @error('terms')
                            <p class="text-xs text-red-500 mt-1 ml-7">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full py-4 px-6 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold rounded-2xl shadow-xl shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2 text-base mt-2">
                    Créer mon compte
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-slate-400">
                En créant un compte vous acceptez nos <a href="{{ route('legal', 'conditions-generales') }}" class="text-slate-600 hover:text-primary-600 underline underline-offset-2">CGV</a>
            </p>
        </div>
    </div>

    {{-- Panneau droit : visuel --}}
    <div class="hidden lg:flex lg:w-[42%] relative overflow-hidden bg-gradient-to-br from-slate-900 via-primary-950 to-slate-900 flex-col justify-between p-14 order-1 lg:order-2">

        {{-- Fond décoratif --}}
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute inset-0 opacity-20"
                 style="background-image: radial-gradient(circle at 70% 30%, #f59e0b 0%, transparent 50%), radial-gradient(circle at 30% 80%, #6366f1 0%, transparent 50%)">
            </div>
            <div class="absolute inset-0 opacity-10"
                 style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 32px 32px;">
            </div>
            <div class="absolute -top-32 -right-32 w-96 h-96 bg-primary-600/30 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-accent-500/20 rounded-full blur-3xl"></div>
        </div>

        {{-- Logo --}}
        <div class="relative z-10">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <div class="w-10 h-10 bg-white/15 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg tracking-tight">{{ $siteName }}</span>
            </a>
        </div>

        {{-- Contenu --}}
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full text-white/80 text-xs font-semibold uppercase tracking-widest mb-6">
                <span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>
                Nouveau membre
            </div>
            <h2 class="text-5xl font-black text-white leading-tight mb-4">
                Rejoignez<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-400">la communauté</span>
            </h2>
            <p class="text-white/70 text-lg leading-relaxed max-w-xs">
                Des milliers de clients font confiance au Grand Bazar pour leurs achats quotidiens.
            </p>

            {{-- Stats animées --}}
            <div class="mt-10 grid grid-cols-3 gap-4"
                 x-data="{ stats: [{from:0,to:10,suffix:'K+'},{from:0,to:50,suffix:'K+'},{from:0,to:98,suffix:'%'}], display:[0,0,0] }"
                 x-init="stats.forEach((s, i) => {
                    let step = (s.to - s.from) / 60;
                    let cur = s.from;
                    let timer = setInterval(() => {
                        cur += step;
                        if (cur >= s.to) { cur = s.to; clearInterval(timer); }
                        display[i] = Math.floor(cur);
                    }, 25);
                 })">
                @foreach([['suffix' => 'K+', 'label' => 'Clients'], ['suffix' => 'K+', 'label' => 'Commandes'], ['suffix' => '%', 'label' => 'Satisfaits']] as $idx => $stat)
                <div class="bg-white/8 backdrop-blur-sm border border-white/15 rounded-xl p-3 text-center hover:bg-white/12 transition-colors">
                    <div class="text-2xl font-black text-white"><span x-text="display[{{ $idx }}]">0</span>{{ $stat['suffix'] }}</div>
                    <div class="text-xs text-white/60 mt-0.5">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>

            {{-- Avantages compte --}}
            <div class="mt-8 space-y-3">
                @foreach([
                    ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Accès à votre historique complet'],
                    ['icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'text' => 'Suivi de commande en temps réel'],
                    ['icon' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7', 'text' => 'Offres et réductions exclusives'],
                ] as $item)
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-amber-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-white/75 text-sm">{{ $item['text'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Badge sécurité --}}
        <div class="relative z-10 flex items-center gap-3 bg-white/8 backdrop-blur-sm border border-white/15 rounded-2xl px-4 py-3">
            <div class="w-10 h-10 bg-emerald-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <p class="text-white text-sm font-semibold">Données sécurisées</p>
                <p class="text-white/55 text-xs">Vos informations sont protégées et jamais revendues</p>
            </div>
        </div>
    </div>
</div>
@endsection
