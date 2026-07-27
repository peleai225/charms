@extends('layouts.front')

@section('title', 'Nous contacter')
@section('meta_description', 'Contactez notre équipe pour toute question sur vos commandes, produits ou livraisons. Réponse rapide garantie.')

@section('content')

{{-- Breadcrumb --}}
<div class="bg-white border-b border-slate-100">
    <div class="container mx-auto px-4 py-3">
        <nav class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-slate-900 transition-colors">Accueil</a>
            <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 font-medium">Contact</span>
        </nav>
    </div>
</div>

<div class="container mx-auto px-4 py-10 max-w-5xl">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Nous contacter</h1>
        <p class="text-slate-500 text-sm mt-1">Notre équipe est disponible pour répondre à toutes vos questions.</p>
    </div>

    @php
        $contactEmail   = \App\Models\Setting::get('contact_email',   'contact@chamse.ci');
        $contactPhone   = \App\Models\Setting::get('contact_phone',   '+225 07 00 00 00 00');
        $contactAddress = \App\Models\Setting::get('contact_address', 'Abidjan, Cocody, Côte d\'Ivoire');
        $whatsappNumber = preg_replace('/[^0-9]/', '', config('app.whatsapp_number', '2250506805382'));
    @endphp

    <div class="grid lg:grid-cols-2 gap-8">

        {{-- Left: Form --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 mb-5">Envoyez-nous un message</h2>

            @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
                {{ session('error') }}
            </div>
            @endif

            <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nom complet <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               placeholder="Votre nom"
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors @error('name') border-red-300 @enderror">
                        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               placeholder="votre@email.com"
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors @error('email') border-red-300 @enderror">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-slate-700 mb-1.5">Sujet <span class="text-red-500">*</span></label>
                    <select name="subject" id="subject" required
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 transition-colors @error('subject') border-red-300 @enderror">
                        <option value="">Choisir un sujet</option>
                        <option value="order"       {{ old('subject') === 'order'       ? 'selected' : '' }}>Question sur une commande</option>
                        <option value="product"     {{ old('subject') === 'product'     ? 'selected' : '' }}>Question sur un produit</option>
                        <option value="return"      {{ old('subject') === 'return'      ? 'selected' : '' }}>Retour / Remboursement</option>
                        <option value="delivery"    {{ old('subject') === 'delivery'    ? 'selected' : '' }}>Problème de livraison</option>
                        <option value="partnership" {{ old('subject') === 'partnership' ? 'selected' : '' }}>Partenariat</option>
                        <option value="other"       {{ old('subject') === 'other'       ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('subject')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-slate-700 mb-1.5">Message <span class="text-red-500">*</span></label>
                    <textarea name="message" id="message" rows="5" required
                              placeholder="Votre message..."
                              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600 resize-none transition-colors @error('message') border-red-300 @enderror">{{ old('message') }}</textarea>
                    @error('message')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm rounded-xl transition-colors">
                    Envoyer le message
                </button>
            </form>
        </div>

        {{-- Right: Contact info --}}
        <div class="space-y-4">

            {{-- WhatsApp CTA --}}
            <div class="bg-[#25D366] rounded-2xl p-5 text-white">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Réponse rapide sur WhatsApp</p>
                        <p class="text-white/80 text-xs">Disponible 7j/7</p>
                    </div>
                </div>
                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode('Bonjour, j\'ai une question.') }}"
                   target="_blank" rel="noopener"
                   class="block w-full text-center py-2.5 bg-white text-[#25D366] font-bold text-sm rounded-xl hover:bg-white/90 transition-colors">
                    Écrire sur WhatsApp
                </a>
            </div>

            {{-- Contact details --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                {{-- Phone --}}
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-primary-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-0.5">Téléphone</p>
                        @if($contactPhone)
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="text-sm font-semibold text-primary-600 hover:underline">{{ $contactPhone }}</a>
                        @else
                        <p class="text-sm text-slate-400">Non renseigné</p>
                        @endif
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Email --}}
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-slate-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-0.5">Email</p>
                        @if($contactEmail)
                        <a href="mailto:{{ $contactEmail }}" class="text-sm font-semibold text-primary-600 hover:underline">{{ $contactEmail }}</a>
                        @else
                        <p class="text-sm text-slate-400">Non renseigné</p>
                        @endif
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Address --}}
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-slate-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-0.5">Adresse</p>
                        <p class="text-sm text-slate-700">{!! nl2br(e($contactAddress)) !!}</p>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Hours --}}
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-slate-50 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-0.5">Horaires</p>
                        <p class="text-sm text-slate-700">Lun–Sam : 8h–20h</p>
                        <p class="text-sm text-slate-700">Dim : 10h–18h</p>
                    </div>
                </div>
            </div>

            {{-- FAQ rapide --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm" x-data="{ open: null }">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Questions fréquentes</h3>
                @php
                $faqs = [
                    ['q' => 'Délais de livraison ?', 'a' => '24–72h pour Abidjan, 3–7 jours pour les autres villes.'],
                    ['q' => 'Comment suivre ma commande ?', 'a' => 'Via la page "Suivi de commande" avec votre numéro reçu par SMS/WhatsApp.'],
                    ['q' => 'Modes de paiement acceptés ?', 'a' => 'Orange Money, MTN MoMo, carte bancaire (CinetPay) et paiement à la livraison.'],
                ];
                @endphp
                <div class="space-y-1.5">
                    @foreach($faqs as $i => $faq)
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                                class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-slate-50 transition-colors">
                            <span class="text-xs font-semibold text-slate-800">{{ $faq['q'] }}</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200 flex-shrink-0 ml-2" :class="{ 'rotate-180': open === {{ $i }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse x-cloak>
                            <p class="px-4 pb-3 text-xs text-slate-500">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
