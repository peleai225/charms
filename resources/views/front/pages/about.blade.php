@extends('layouts.front')

@section('title', 'À propos de nous')

@section('content')
<!-- Hero header -->
<div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white py-16 relative overflow-hidden">
    <div class="absolute -top-20 -right-20 w-80 h-80 bg-primary-600/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-violet-600/10 rounded-full blur-3xl"></div>
    <div class="container mx-auto px-4 relative">
        <nav class="text-sm text-slate-400 mb-4 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <span class="text-white">À propos</span>
        </nav>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4">
            Notre <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 via-violet-400 to-amber-400">Histoire</span>
        </h1>
        <p class="text-lg text-slate-300 max-w-2xl">
            Chamse est né d'une passion : offrir des produits de qualité accessibles à tous,
            avec une expérience d'achat exceptionnelle.
        </p>
    </div>
</div>

<div class="container mx-auto px-4 py-12">

    <!-- Mission Section -->
    <div class="grid lg:grid-cols-2 gap-12 items-center mb-20">
        <div>
            <span class="inline-block px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-medium mb-4">
                Notre Mission
            </span>
            <h2 class="text-3xl font-bold text-slate-900 mb-6">
                Rendre le shopping en ligne simple et agréable
            </h2>
            <p class="text-slate-600 mb-4">
                Nous croyons que chaque client mérite une expérience d'achat exceptionnelle. 
                C'est pourquoi nous sélectionnons soigneusement chaque produit et nous 
                assurons un service client irréprochable.
            </p>
            <p class="text-slate-600">
                Notre plateforme combine technologie moderne et attention personnalisée 
                pour vous offrir le meilleur du e-commerce.
            </p>
        </div>
        <div class="relative">
            <div class="aspect-video bg-gradient-to-br from-primary-100 to-primary-200 rounded-3xl flex items-center justify-center">
                <div class="text-center p-8">
                    <div class="w-20 h-20 bg-white rounded-2xl shadow-lg flex items-center justify-center mx-auto mb-4">
                        <span class="text-4xl font-bold text-primary-600">C</span>
                    </div>
                    <p class="text-primary-800 font-medium">Chamse E-Commerce</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Values Section -->
    <div class="mb-20">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-medium mb-4">
                Nos Valeurs
            </span>
            <h2 class="text-3xl font-bold text-slate-900">Ce qui nous définit</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Qualité -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-3">Qualité</h3>
                <p class="text-slate-600">
                    Nous sélectionnons rigoureusement chaque produit pour garantir une qualité irréprochable.
                </p>
            </div>

            <!-- Confiance -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-3">Confiance</h3>
                <p class="text-slate-600">
                    Paiements sécurisés, données protégées : votre sécurité est notre priorité absolue.
                </p>
            </div>

            <!-- Service -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-3">Service</h3>
                <p class="text-slate-600">
                    Une équipe dédiée à votre satisfaction, disponible pour répondre à toutes vos questions.
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-primary-900 rounded-3xl p-12 text-white mb-20 shadow-2xl">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <p class="text-4xl font-bold mb-2">{{ \App\Models\Product::active()->count() }}+</p>
                <p class="text-primary-200">Produits</p>
            </div>
            <div>
                <p class="text-4xl font-bold mb-2">{{ \App\Models\Customer::count() }}+</p>
                <p class="text-primary-200">Clients satisfaits</p>
            </div>
            <div>
                <p class="text-4xl font-bold mb-2">{{ \App\Models\Order::where('status', 'delivered')->count() }}+</p>
                <p class="text-primary-200">Commandes livrées</p>
            </div>
            <div>
                <p class="text-4xl font-bold mb-2">24/7</p>
                <p class="text-primary-200">Support client</p>
            </div>
        </div>
    </div>

    <!-- Team Section (Optional) -->
    <div class="text-center mb-12">
        <span class="inline-block px-4 py-2 bg-amber-100 text-amber-700 rounded-full text-sm font-medium mb-4">
            Pourquoi nous choisir ?
        </span>
        <h2 class="text-3xl font-bold text-slate-900 mb-12">Les avantages Chamse</h2>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6">
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-900 mb-2">Livraison rapide</h3>
                <p class="text-sm text-slate-600">Expédition sous 24-48h</p>
            </div>

            <div class="p-6">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-900 mb-2">Retours gratuits</h3>
                <p class="text-sm text-slate-600">30 jours pour changer d'avis</p>
            </div>

            <div class="p-6">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-900 mb-2">Meilleurs prix</h3>
                <p class="text-sm text-slate-600">Garantie prix bas</p>
            </div>

            <div class="p-6">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-900 mb-2">Support réactif</h3>
                <p class="text-sm text-slate-600">Réponse sous 24h</p>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="text-center py-14 bg-gradient-to-br from-slate-50 to-primary-50 rounded-3xl border border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900 mb-4">Prêt à découvrir nos produits ?</h2>
        <p class="text-slate-600 mb-8">Explorez notre catalogue et trouvez ce qu'il vous faut.</p>
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-primary-600 text-white font-semibold rounded-2xl hover:bg-primary-700 shadow-lg shadow-primary-500/25 hover:-translate-y-0.5 transition-all">
            Découvrir la boutique
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
</div>
@endsection

