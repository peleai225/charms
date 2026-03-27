@extends('layouts.front')

@section('title', 'Connexion')
@section('hide_site_chrome', '1')

@section('content')
<div class="min-h-screen flex" x-data="{ showPass: false }">

    {{-- Panneau gauche : visuel immersif --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-slate-900 via-primary-950 to-slate-900 flex-col justify-between p-14">

        {{-- Fond décoratif --}}
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full opacity-20"
                 style="background-image: radial-gradient(circle at 20% 20%, #f59e0b 0%, transparent 50%), radial-gradient(circle at 80% 80%, #6366f1 0%, transparent 50%)">
            </div>
            {{-- Grille de points --}}
            <div class="absolute inset-0 opacity-10"
                 style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 32px 32px;">
            </div>
            {{-- Blob décoratif --}}
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-primary-600/30 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-accent-500/20 rounded-full blur-3xl"></div>
        </div>

        {{-- Logo --}}
        <div class="relative z-10">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <div class="w-10 h-10 bg-white/15 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg tracking-tight">Le Grand Bazar</span>
            </a>
        </div>

        {{-- Contenu central --}}
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full text-white/80 text-xs font-semibold uppercase tracking-widest mb-6">
                <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                Espace client sécurisé
            </div>
            <h2 class="text-5xl font-black text-white leading-tight mb-4">
                Heureux de<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-400">vous revoir</span>
            </h2>
            <p class="text-white/70 text-lg leading-relaxed max-w-xs">
                Accédez à votre espace pour suivre vos commandes et profiter de vos offres exclusives.
            </p>

            {{-- Avantages --}}
            <div class="mt-10 space-y-4">
                @foreach([
                    ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Suivi en temps réel de vos commandes'],
                    ['icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'text' => 'Votre liste de favoris sauvegardée'],
                    ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Paiement express à chaque commande'],
                ] as $item)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="text-white/75 text-sm">{{ $item['text'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Témoignage --}}
        <div class="relative z-10">
            <div class="bg-white/8 backdrop-blur-sm border border-white/15 rounded-2xl p-5">
                <div class="flex items-center gap-1 mb-2">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    @endfor
                </div>
                <p class="text-white/80 text-sm italic leading-relaxed">"Service excellent, livraison rapide et produits de qualité. Je recommande vivement !"</p>
                <p class="text-white/50 text-xs mt-2 font-medium">— Aminata K., Abidjan</p>
            </div>
        </div>
    </div>

    {{-- Panneau droit : formulaire --}}
    <div class="flex-1 flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-16 bg-white">
        <div class="max-w-md w-full mx-auto">

            {{-- Logo mobile --}}
            <div class="lg:hidden mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-slate-900 font-bold text-lg">
                    <div class="w-9 h-9 bg-primary-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    Le Grand Bazar
                </a>
            </div>

            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-900 mb-2">Connexion</h1>
                <p class="text-slate-500">
                    Pas encore de compte ?
                    <a href="{{ route('register') }}" class="text-primary-600 font-semibold hover:text-primary-700 transition-colors">Créez-en un gratuitement →</a>
                </p>
            </div>

            @if (session('error'))
            <div class="mb-5 flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-red-700 text-sm">{{ session('error') }}</p>
            </div>
            @endif

            @if (session('success'))
            <div class="mb-5 flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-emerald-700 text-sm">{{ session('success') }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5 no-ajax">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Adresse email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border @error('email') border-red-400 bg-red-50 @else border-slate-200 @enderror rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 focus:bg-white transition-all"
                            placeholder="votre@email.com" required autofocus>
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Mot de passe --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="text-sm font-semibold text-slate-700">Mot de passe</label>
                        <a href="{{ route('password.request') }}" class="text-sm text-primary-600 hover:text-primary-700 transition-colors font-medium">
                            Oublié ?
                        </a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" name="password" id="password"
                            class="w-full pl-12 pr-12 py-3.5 bg-slate-50 border @error('password') border-red-400 bg-red-50 @else border-slate-200 @enderror rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 focus:bg-white transition-all"
                            placeholder="••••••••" required>
                        <button type="button" @click="showPass = !showPass"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" name="remember" id="remember" class="sr-only peer">
                        <div class="w-5 h-5 bg-slate-100 border-2 border-slate-300 rounded-md peer-checked:bg-primary-600 peer-checked:border-primary-600 transition-all group-hover:border-primary-400"></div>
                        <svg class="absolute inset-0 w-5 h-5 text-white opacity-0 peer-checked:opacity-100 transition-opacity p-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-sm text-slate-600 select-none">Se souvenir de moi</span>
                </label>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full py-4 px-6 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold rounded-2xl shadow-xl shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2 text-base">
                    Se connecter
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>

                {{-- Séparateur --}}
                <div class="relative flex items-center gap-3">
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">ou continuer en tant qu'invité</span>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>

                <a href="{{ route('shop.index') }}"
                   class="w-full py-3.5 px-6 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold rounded-2xl border border-slate-200 hover:border-slate-300 transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Continuer sans compte
                </a>
            </form>

            {{-- Footer --}}
            <p class="mt-8 text-center text-xs text-slate-400">
                En vous connectant vous acceptez nos
                <a href="#" class="text-slate-600 hover:text-primary-600 underline underline-offset-2">CGV</a> et notre
                <a href="#" class="text-slate-600 hover:text-primary-600 underline underline-offset-2">politique de confidentialité</a>
            </p>
        </div>
    </div>
</div>
@endsection
