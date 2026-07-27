<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion SuperAdmin - {{ config('app.name') }}</title>
    @if(app()->environment('local'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        @php
            try {
                if (class_exists('\App\Helpers\ViteHelper')) {
                    $viteHelper = new \App\Helpers\ViteHelper();
                    echo $viteHelper->renderAssets(['resources/css/app.css', 'resources/js/app.js']);
                } else {
                    $buildPath = public_path('build/assets');
                    if (is_dir($buildPath)) {
                        $files = scandir($buildPath);
                        foreach ($files as $file) {
                            if ($file !== '.' && $file !== '..') {
                                if (str_ends_with($file, '.css')) {
                                    echo '<link rel="stylesheet" href="' . asset('build/assets/' . $file) . '">' . "\n    ";
                                } elseif (str_ends_with($file, '.js')) {
                                    echo '<script type="module" src="' . asset('build/assets/' . $file) . '"></script>' . "\n    ";
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $buildPath = public_path('build/assets');
                if (is_dir($buildPath)) {
                    $files = scandir($buildPath);
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..') {
                            if (str_ends_with($file, '.css')) {
                                echo '<link rel="stylesheet" href="' . asset('build/assets/' . $file) . '">' . "\n    ";
                            } elseif (str_ends_with($file, '.js')) {
                                echo '<script type="module" src="' . asset('build/assets/' . $file) . '"></script>' . "\n    ";
                            }
                        }
                    }
                }
            }
        @endphp
    @endif
</head>
<body class="min-h-screen bg-white">

    <div class="flex min-h-screen">
        <!-- Left Side - Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-12">
            <div class="w-full max-w-md">

                <!-- Logo -->
                <div class="mb-8">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-fuchsia-600 flex items-center justify-center shadow-lg shadow-purple-500/30">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-slate-900">{{ \App\Models\Setting::get('site_name', config('app.name', 'Boutique')) }}</span>
                        <span class="px-2 py-0.5 text-xs font-bold bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white rounded-full">SUPER</span>
                    </div>
                </div>

                <!-- Title -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-slate-900 mb-2">Super Administration</h1>
                    <p class="text-slate-600">Accès privilégié aux fonctions avancées</p>
                </div>

                <!-- Alert Warning -->
                <div class="mb-6 p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>Zone à accès restreint. Toute connexion est enregistrée.</span>
                </div>

                <!-- Messages flash -->
                @if (session('error'))
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('superadmin.login.post') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                            Adresse email<span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 bg-white border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('email') border-red-500 @enderror"
                            placeholder="superadmin@example.com"
                            required
                            autofocus
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                            Mot de passe<span class="text-red-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="w-full px-4 py-3 bg-white border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('password') border-red-500 @enderror"
                            placeholder="Entrez votre mot de passe"
                            required
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500 focus:ring-offset-0"
                        >
                        <label for="remember" class="ml-2 text-sm text-slate-600 cursor-pointer">
                            Se souvenir de moi
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-purple-600 to-fuchsia-600 hover:from-purple-700 hover:to-fuchsia-700 text-white font-semibold rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-purple-500/30 hover:shadow-purple-500/40"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Accès sécurisé
                    </button>
                </form>

                <!-- Footer Links -->
                <div class="mt-8 pt-6 border-t border-slate-200">
                    <div class="flex items-center justify-between text-sm">
                        <a href="{{ route('home') }}" class="text-slate-600 hover:text-slate-900 transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Retour au site
                        </a>
                        <a href="{{ route('admin.login') }}" class="text-slate-600 hover:text-slate-900 transition-colors">
                            Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Hero -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-purple-900 via-fuchsia-900 to-purple-900 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center items-center text-center p-12 text-white">
                <div class="max-w-lg">
                    <!-- Badge -->
                    <div class="mb-6 inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full">
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="text-sm font-semibold">Accès Privilégié</span>
                    </div>

                    <!-- Icon -->
                    <div class="mb-8 inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>

                    <!-- Title -->
                    <h2 class="text-4xl font-bold mb-4 leading-tight">
                        Contrôle total de la plateforme
                    </h2>

                    <!-- Description -->
                    <p class="text-lg text-purple-200 mb-8">
                        Accédez aux fonctionnalités avancées réservées aux super administrateurs. Gestion complète des utilisateurs, systèmes et configurations.
                    </p>

                    <!-- Features Grid -->
                    <div class="grid grid-cols-2 gap-4 text-left">
                        <div class="flex items-start gap-3 bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-purple-500/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-sm mb-1">Gestion utilisateurs</div>
                                <div class="text-xs text-purple-300">Admins, managers, staff</div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-fuchsia-500/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-fuchsia-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-sm mb-1">Configuration</div>
                                <div class="text-xs text-purple-300">Système & paramètres</div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-pink-500/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-pink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-sm mb-1">Sécurité max</div>
                                <div class="text-xs text-purple-300">Logs & monitoring</div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-500/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-sm mb-1">Accès complet</div>
                                <div class="text-xs text-purple-300">Toutes les fonctions</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-fuchsia-500/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl"></div>
        </div>
    </div>

</body>
</html>
