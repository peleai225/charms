@extends('layouts.front')

@section('title', 'Créer un compte')

@section('content')
<div class="min-h-[80vh] flex">
    <!-- Left side - decorative -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-600 via-primary-700 to-slate-900 relative overflow-hidden items-center justify-center p-12">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-20 left-20 w-72 h-72 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-20 w-96 h-96 bg-accent-500 rounded-full blur-3xl"></div>
        </div>
        <div class="relative text-white text-center max-w-md">
            <h2 class="text-4xl font-bold mb-4">Rejoignez-nous</h2>
            <p class="text-white/80 text-lg">Creez votre compte et decouvrez un monde de shopping exclusif.</p>
        </div>
    </div>
    <!-- Right side - form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center py-12 px-6 sm:px-12 bg-white">
        <div class="w-full max-w-lg">

            <!-- En-tête -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Créer un compte</h1>
                <p class="text-gray-600 mt-2">
                    Déjà inscrit ?
                    <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        Connectez-vous
                    </a>
                </p>
            </div>

            <!-- Messages flash -->
            @if (session('error'))
                <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="space-y-5">
                @csrf

                <!-- Prénom et Nom -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Prénom <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="first_name"
                            id="first_name"
                            value="{{ old('first_name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                            placeholder="Jean"
                            required
                        >
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Nom <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="last_name"
                            id="last_name"
                            value="{{ old('last_name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                            placeholder="Dupont"
                            required
                        >
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Adresse email <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                        placeholder="votre@email.com"
                        required
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Téléphone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Téléphone <span class="text-gray-400">(optionnel)</span>
                    </label>
                    <input
                        type="tel"
                        name="phone"
                        id="phone"
                        value="{{ old('phone') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                        placeholder="06 12 34 56 78"
                    >
                    @error('phone')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                        placeholder="••••••••"
                        required
                    >
                    <p class="mt-1 text-xs text-gray-500">Minimum 8 caractères, avec majuscule et chiffre</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmation mot de passe -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Confirmer le mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                        placeholder="••••••••"
                        required
                    >
                </div>

                <!-- Newsletter -->
                <div class="flex items-start">
                    <input
                        type="checkbox"
                        name="newsletter"
                        id="newsletter"
                        value="1"
                        class="w-4 h-4 mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                    >
                    <label for="newsletter" class="ml-2 text-sm text-gray-600">
                        Je souhaite recevoir les offres promotionnelles et nouveautés par email
                    </label>
                </div>

                <!-- CGV -->
                <div class="flex items-start">
                    <input
                        type="checkbox"
                        name="terms"
                        id="terms"
                        value="1"
                        class="w-4 h-4 mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                        required
                    >
                    <label for="terms" class="ml-2 text-sm text-gray-600">
                        J'accepte les <a href="#" class="text-primary-600 hover:underline">conditions générales de vente</a>
                        et la <a href="#" class="text-primary-600 hover:underline">politique de confidentialité</a>
                        <span class="text-red-500">*</span>
                    </label>
                </div>
                @error('terms')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror

                <!-- Bouton d'inscription -->
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200"
                >
                    Créer mon compte
                </button>
            </form>

            <!-- Séparateur -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500">Ou s'inscrire avec</span>
                </div>
            </div>

            <!-- Inscription sociale -->
            <div class="grid grid-cols-2 gap-3">
                <button type="button" class="flex items-center justify-center gap-2 py-2.5 px-4 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Google</span>
                </button>
                <button type="button" class="flex items-center justify-center gap-2 py-2.5 px-4 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Facebook</span>
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

