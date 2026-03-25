@extends('layouts.front')

@section('title', 'Contact')
@section('meta_description', 'Contactez notre équipe pour toute question sur vos commandes, produits ou livraisons. Réponse rapide garantie. Service client disponible du lundi au vendredi.')

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white py-16 relative overflow-hidden">
    <div class="absolute -top-20 -right-20 w-80 h-80 bg-primary-600/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-violet-600/10 rounded-full blur-3xl"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
    <div class="container mx-auto px-4 relative">
        <nav class="text-sm text-slate-400 mb-5 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Accueil</a>
            <span class="text-slate-600">/</span>
            <span class="text-white">Contact</span>
        </nav>
        <div class="max-w-2xl">
            <h1 class="text-4xl font-extrabold mb-4">Contactez-nous</h1>
            <p class="text-lg text-slate-200 leading-relaxed">
                Vous avez une question ? N'hésitez pas à nous contacter. Notre équipe est là pour vous aider.
            </p>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-6xl mx-auto">

        @php
            $contactEmail = \App\Models\Setting::get('contact_email', 'contact@chamse.ci');
            $contactPhone = \App\Models\Setting::get('contact_phone', '+225 07 00 00 00 00');
            $contactAddress = \App\Models\Setting::get('contact_address', 'Abidjan, Cocody, Côte d\'Ivoire');
        @endphp

        <div class="grid lg:grid-cols-3 gap-5 mb-12">
            <!-- Téléphone -->
            <div class="relative bg-white rounded-2xl p-7 shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-primary-500 to-primary-600 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-primary-100 transition-colors">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 mb-1">Téléphone</h3>
                        <p class="text-xs text-slate-500 mb-3">Lun - Ven, 8h - 18h</p>
                        @if($contactPhone)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="text-sm text-primary-600 font-semibold hover:underline">{{ $contactPhone }}</a>
                        @else
                            <p class="text-sm text-slate-400">Non renseigné</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Email -->
            <div class="relative bg-white rounded-2xl p-7 shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-500 to-emerald-600 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-100 transition-colors">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 mb-1">Email</h3>
                        <p class="text-xs text-slate-500 mb-3">Réponse sous 24h</p>
                        @if($contactEmail)
                            <a href="mailto:{{ $contactEmail }}" class="text-sm text-primary-600 font-semibold hover:underline">{{ $contactEmail }}</a>
                        @else
                            <p class="text-sm text-slate-400">Non renseigné</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Adresse -->
            <div class="relative bg-white rounded-2xl p-7 shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-amber-500 to-amber-600 scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-amber-100 transition-colors">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 mb-1">Adresse</h3>
                        <p class="text-xs text-slate-500 mb-3">Notre bureau</p>
                        @if($contactAddress)
                            <p class="text-sm text-slate-700 leading-relaxed">{!! nl2br(e($contactAddress)) !!}</p>
                        @else
                            <p class="text-sm text-slate-400">Non renseignée</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire de contact -->
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
            <h2 class="text-xl font-bold text-slate-900 mb-6">Envoyez-nous un message</h2>

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-5">
                @csrf
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nom complet</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-slate-50 focus:bg-white text-sm @error('name') border-red-300 @enderror"
                            placeholder="Votre nom">
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-slate-50 focus:bg-white text-sm @error('email') border-red-300 @enderror"
                            placeholder="votre@email.com">
                        @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-slate-700 mb-1.5">Sujet</label>
                    <select id="subject" name="subject" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-slate-50 focus:bg-white text-sm @error('subject') border-red-300 @enderror">
                        <option value="">Choisir un sujet</option>
                        <option value="order" {{ old('subject') === 'order' ? 'selected' : '' }}>Question sur une commande</option>
                        <option value="product" {{ old('subject') === 'product' ? 'selected' : '' }}>Question sur un produit</option>
                        <option value="return" {{ old('subject') === 'return' ? 'selected' : '' }}>Retour / Remboursement</option>
                        <option value="partnership" {{ old('subject') === 'partnership' ? 'selected' : '' }}>Partenariat</option>
                        <option value="other" {{ old('subject') === 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('subject')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-slate-700 mb-1.5">Message</label>
                    <textarea id="message" name="message" rows="5" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all resize-none bg-slate-50 focus:bg-white text-sm @error('message') border-red-300 @enderror"
                        placeholder="Votre message...">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full md:w-auto px-8 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-all shadow-lg shadow-primary-500/20 hover:-translate-y-0.5 text-sm">
                    Envoyer le message
                </button>
            </form>
        </div>

        <!-- FAQ rapide -->
        <div class="mt-14">
            <h2 class="text-xl font-bold text-slate-900 mb-6 text-center">Questions fréquentes</h2>

            <div class="max-w-3xl mx-auto space-y-2.5" x-data="{ open: null }">
                @php
                    $faqs = [
                        ['q' => 'Quels sont les délais de livraison ?', 'a' => 'La livraison est effectuée sous 24 à 72h pour Abidjan, et sous 3 à 7 jours pour les autres villes de Côte d\'Ivoire.'],
                        ['q' => 'Comment suivre ma commande ?', 'a' => 'Connectez-vous à votre compte client et accédez à la section "Mes commandes" pour voir le statut de vos commandes.'],
                        ['q' => 'Quels modes de paiement acceptez-vous ?', 'a' => 'Nous acceptons Orange Money, MTN Mobile Money, les cartes bancaires via CinetPay, et le paiement à la livraison.'],
                        ['q' => 'Puis-je retourner un article ?', 'a' => 'Oui, vous disposez de 30 jours pour retourner un article non utilisé dans son emballage d\'origine.'],
                    ];
                @endphp

                @foreach($faqs as $i => $faq)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <button @click="open = open === {{ $i + 1 }} ? null : {{ $i + 1 }}" class="w-full flex items-center justify-between p-5 text-left hover:bg-slate-50/50 transition-colors">
                        <h3 class="font-semibold text-sm text-slate-900">{{ $faq['q'] }}</h3>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === {{ $i + 1 }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open === {{ $i + 1 }}" x-collapse x-cloak>
                        <div class="px-5 pb-5 text-slate-500 text-sm leading-relaxed">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
