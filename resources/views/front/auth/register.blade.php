@extends('layouts.front')

@section('title', 'Créer un compte')

@section('content')
<div class="min-h-[80vh] flex">
    <!-- Left side - decorative -->
    <div class="hidden lg:flex lg:w-5/12 bg-gradient-to-br from-slate-900 via-slate-800 to-primary-900 relative overflow-hidden items-center justify-center p-12">
        <!-- Decorative orbs -->
        <div class="absolute top-20 left-20 w-72 h-72 bg-primary-500/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-violet-500/15 rounded-full blur-3xl"></div>
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>

        <div class="relative text-white max-w-sm">
            <div class="w-14 h-14 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-8 border border-white/10">
                <svg class="w-7 h-7 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-bold mb-3">Rejoignez-nous</h2>
            <p class="text-slate-300 text-base leading-relaxed mb-10">Créez votre compte en quelques secondes et profitez d'une expérience shopping personnalisée.</p>

            <!-- Trust features -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-slate-300">Inscription gratuite et rapide</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-slate-300">Accès aux promotions membres</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-slate-300">Programme de fidélité avec récompenses</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-slate-300">Données personnelles protégées</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right side - form -->
    <div class="w-full lg:w-7/12 flex items-center justify-center py-10 px-6 sm:px-12 bg-white">
        <div class="w-full max-w-lg">

            <!-- En-tête -->
            <div class="mb-7">
                <h1 class="text-2xl font-bold text-slate-900">Créer un compte</h1>
                <p class="text-slate-500 mt-1.5 text-sm">
                    Déjà inscrit ?
                    <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-semibold">
                        Connectez-vous
                    </a>
                </p>
            </div>

            <!-- Messages flash -->
            @if (session('error'))
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-100 text-red-600 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
                @csrf

                <!-- Prénom et Nom -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-slate-700 mb-1.5">Prénom</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-slate-50 focus:bg-white text-sm"
                            placeholder="Jean" required>
                        @error('first_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-slate-700 mb-1.5">Nom</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-slate-50 focus:bg-white text-sm"
                            placeholder="Dupont" required>
                        @error('last_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Adresse email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-slate-50 focus:bg-white text-sm"
                            placeholder="votre@email.com" required>
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Téléphone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Téléphone <span class="text-slate-400 font-normal">(optionnel)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                            class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-slate-50 focus:bg-white text-sm"
                            placeholder="+225 07 00 00 00 00">
                    </div>
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Mot de passe</label>
                        <input type="password" name="password" id="password"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-slate-50 focus:bg-white text-sm"
                            placeholder="Min. 8 caractères" required>
                        @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Confirmation</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-slate-50 focus:bg-white text-sm"
                            placeholder="Confirmez" required>
                    </div>
                </div>

                <!-- Newsletter + CGV -->
                <div class="space-y-3 pt-1">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="newsletter" value="1"
                            class="w-4 h-4 mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-xs text-slate-600 leading-relaxed">
                            Je souhaite recevoir les offres et nouveautés par email
                        </span>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="terms" value="1" required
                            class="w-4 h-4 mt-0.5 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-xs text-slate-600 leading-relaxed">
                            J'accepte les <a href="#" class="text-primary-600 hover:underline font-medium">CGV</a>
                            et la <a href="#" class="text-primary-600 hover:underline font-medium">politique de confidentialité</a>
                        </span>
                    </label>
                    @error('terms')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bouton d'inscription -->
                <button type="submit"
                    class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/20 hover:shadow-primary-500/30 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 text-sm">
                    Créer mon compte
                </button>
            </form>

            <!-- Séparateur -->
            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="px-3 bg-white text-slate-400">ou</span>
                </div>
            </div>

            <!-- Inscription sociale -->
            <div class="grid grid-cols-2 gap-3">
                <button type="button" class="flex items-center justify-center gap-2 py-2.5 px-4 border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-all text-sm">
                    <svg class="w-4.5 h-4.5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="font-medium text-slate-700">Google</span>
                </button>
                <button type="button" class="flex items-center justify-center gap-2 py-2.5 px-4 border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-all text-sm">
                    <svg class="w-4.5 h-4.5" fill="#1877F2" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span class="font-medium text-slate-700">Facebook</span>
                </button>
            </div>

        </div>
    </div>
</div>
@endsection
