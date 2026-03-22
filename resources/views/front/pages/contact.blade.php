@extends('layouts.front')

@section('title', 'Contact')

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white py-14 mb-12">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="text-sm text-slate-400 mb-6">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="mx-2">/</span>
            <span class="text-white">Contact</span>
        </nav>

        <div class="text-center">
            <h1 class="text-4xl font-bold mb-4">Contactez-nous</h1>
            <p class="text-lg text-slate-300 max-w-2xl mx-auto">
                Vous avez une question ? N'hésitez pas à nous contacter. Notre équipe est là pour vous aider.
            </p>
        </div>
    </div>
</div>

<div class="container mx-auto px-4">
    <div class="max-w-6xl mx-auto">

        @php
            $contactEmail = \App\Models\Setting::get('contact_email', 'contact@chamse.ci');
            $contactPhone = \App\Models\Setting::get('contact_phone', '+225 07 00 00 00 00');
            $contactAddress = \App\Models\Setting::get('contact_address', 'Abidjan, Cocody, Côte d\'Ivoire');
        @endphp

        <div class="grid lg:grid-cols-3 gap-8 mb-12">
            <!-- Téléphone -->
            <div class="relative overflow-hidden bg-white rounded-2xl p-8 text-center shadow-sm border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary-500 to-primary-600"></div>
                <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">Téléphone</h3>
                <p class="text-slate-600 mb-4">Du lundi au vendredi, 8h - 18h</p>
                @if($contactPhone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="text-primary-600 font-medium hover:underline">{{ $contactPhone }}</a>
                @else
                    <p class="text-slate-500">Non renseigné</p>
                @endif
            </div>

            <!-- Email -->
            <div class="relative overflow-hidden bg-white rounded-2xl p-8 text-center shadow-sm border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 to-green-600"></div>
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">Email</h3>
                <p class="text-slate-600 mb-4">Réponse sous 24h</p>
                @if($contactEmail)
                    <a href="mailto:{{ $contactEmail }}" class="text-primary-600 font-medium hover:underline">{{ $contactEmail }}</a>
                @else
                    <p class="text-slate-500">Non renseigné</p>
                @endif
            </div>

            <!-- Adresse -->
            <div class="relative overflow-hidden bg-white rounded-2xl p-8 text-center shadow-sm border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-amber-600"></div>
                <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">Adresse</h3>
                <p class="text-slate-600 mb-4">Notre bureau</p>
                @if($contactAddress)
                    <p class="text-slate-700">{!! nl2br(e($contactAddress)) !!}</p>
                @else
                    <p class="text-slate-500">Non renseignée</p>
                @endif
            </div>
        </div>

        <!-- Formulaire de contact -->
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Envoyez-nous un message</h2>
            
            <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Nom complet</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('name') border-red-500 @enderror"
                            placeholder="Votre nom">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('email') border-red-500 @enderror"
                            placeholder="votre@email.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-slate-700 mb-2">Sujet</label>
                    <select id="subject" name="subject" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('subject') border-red-500 @enderror">
                        <option value="">Choisir un sujet</option>
                        <option value="order" {{ old('subject') === 'order' ? 'selected' : '' }}>Question sur une commande</option>
                        <option value="product" {{ old('subject') === 'product' ? 'selected' : '' }}>Question sur un produit</option>
                        <option value="return" {{ old('subject') === 'return' ? 'selected' : '' }}>Retour / Remboursement</option>
                        <option value="partnership" {{ old('subject') === 'partnership' ? 'selected' : '' }}>Partenariat</option>
                        <option value="other" {{ old('subject') === 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('subject')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-slate-700 mb-2">Message</label>
                    <textarea id="message" name="message" rows="5" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none @error('message') border-red-500 @enderror"
                        placeholder="Votre message...">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full md:w-auto px-8 py-4 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors focus:ring-4 focus:ring-primary-500/30">
                    Envoyer le message
                </button>
            </form>
        </div>

        <!-- FAQ rapide -->
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-slate-900 mb-6 text-center">Questions fréquentes</h2>

            <div class="max-w-3xl mx-auto space-y-3" x-data="{ open: null }">
                <!-- Question 1 -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left hover:bg-slate-50 transition-colors">
                        <h3 class="font-semibold text-slate-900">Quels sont les délais de livraison ?</h3>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse x-cloak>
                        <div class="px-6 pb-6 text-slate-600 text-sm">
                            La livraison est effectuée sous 24 à 72h pour Abidjan, et sous 3 à 7 jours pour les autres villes de Côte d'Ivoire.
                        </div>
                    </div>
                </div>

                <!-- Question 2 -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left hover:bg-slate-50 transition-colors">
                        <h3 class="font-semibold text-slate-900">Comment suivre ma commande ?</h3>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse x-cloak>
                        <div class="px-6 pb-6 text-slate-600 text-sm">
                            Connectez-vous à votre compte client et accédez à la section "Mes commandes" pour voir le statut de vos commandes.
                        </div>
                    </div>
                </div>

                <!-- Question 3 -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left hover:bg-slate-50 transition-colors">
                        <h3 class="font-semibold text-slate-900">Quels modes de paiement acceptez-vous ?</h3>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse x-cloak>
                        <div class="px-6 pb-6 text-slate-600 text-sm">
                            Nous acceptons Orange Money, MTN Mobile Money, les cartes bancaires via CinetPay, et le paiement à la livraison.
                        </div>
                    </div>
                </div>

                <!-- Question 4 -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left hover:bg-slate-50 transition-colors">
                        <h3 class="font-semibold text-slate-900">Puis-je retourner un article ?</h3>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse x-cloak>
                        <div class="px-6 pb-6 text-slate-600 text-sm">
                            Oui, vous disposez de 30 jours pour retourner un article non utilisé dans son emballage d'origine.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

