@php
    $fSiteName    = \App\Models\Setting::get('site_name', config('app.name'));
    $fSiteLogo    = \App\Models\Setting::get('site_logo');
    $fDesc        = \App\Models\Setting::get('site_description', 'Votre boutique en ligne de confiance — qualité premium, prix imbattables.');
    $fPhone       = \App\Models\Setting::get('contact_phone');
    $fEmail       = \App\Models\Setting::get('contact_email');
    $fAddress     = \App\Models\Setting::get('contact_address');
    $fFacebook    = \App\Models\Setting::get('social_facebook');
    $fInstagram   = \App\Models\Setting::get('social_instagram');
    $fWhatsapp    = \App\Models\Setting::get('social_whatsapp');
    $fTiktok      = \App\Models\Setting::get('social_tiktok');
    $fFooterCats  = \App\Models\Category::whereNull('parent_id')->orderBy('order')->take(5)->get();
@endphp

<footer class="mt-16">
    {{-- Zone principale --}}
    <div class="bg-slate-900 text-slate-300">
        <div class="container mx-auto px-4 py-14">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

                {{-- Colonne 1 : Brand --}}
                <div>
                    <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2.5 mb-4">
                        @if($fSiteLogo)
                            <img src="{{ asset('storage/' . $fSiteLogo) }}" alt="{{ $fSiteName }}" class="h-9 w-auto brightness-0 invert">
                        @else
                            <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                                <span class="text-white font-bold text-base">{{ substr($fSiteName, 0, 1) }}</span>
                            </div>
                            <span class="text-lg font-semibold text-white">{{ $fSiteName }}</span>
                        @endif
                    </a>
                    <p class="text-slate-400 text-sm leading-relaxed mb-5">{{ $fDesc }}</p>
                    {{-- Réseaux sociaux --}}
                    <div class="flex items-center gap-2">
                        @if($fFacebook)
                        <a href="{{ $fFacebook }}" target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-colors"
                           title="Facebook">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        @endif
                        @if($fInstagram)
                        <a href="{{ $fInstagram }}" target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-colors"
                           title="Instagram">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        @endif
                        @if($fWhatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $fWhatsapp) }}" target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white hover:bg-[#25D366] transition-colors"
                           title="WhatsApp">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        @endif
                        @if($fTiktok)
                        <a href="{{ $fTiktok }}" target="_blank" rel="noopener"
                           class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-colors"
                           title="TikTok">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V9.02a8.16 8.16 0 004.77 1.52V7.1a4.85 4.85 0 01-1-.41z"/></svg>
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Colonne 2 : Boutique --}}
                <div>
                    <h4 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Boutique</h4>
                    <ul class="space-y-2.5">
                        @foreach($fFooterCats as $cat)
                        <li>
                            <a href="{{ route('shop.category', $cat->slug) }}" wire:navigate class="text-sm text-slate-400 hover:text-white transition-colors">
                                {{ $cat->name }}
                            </a>
                        </li>
                        @endforeach
                        <li class="pt-1">
                            <a href="{{ route('shop.index') }}" wire:navigate class="text-sm text-blue-400 hover:text-blue-300 font-medium transition-colors">
                                Voir tout →
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Colonne 3 : Service client --}}
                <div>
                    <h4 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Service client</h4>
                    <ul class="space-y-2.5">
                        <li><a href="{{ route('order-tracking.index') }}" wire:navigate class="text-sm text-slate-400 hover:text-white transition-colors">Suivi de commande</a></li>
                        <li><a href="{{ route('contact') }}" wire:navigate class="text-sm text-slate-400 hover:text-white transition-colors">Nous contacter</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Retours & remboursements</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Livraison</a></li>
                    </ul>
                    @if($fPhone || $fEmail)
                    <div class="mt-5 space-y-2">
                        @if($fPhone)
                        <a href="tel:{{ $fPhone }}" class="flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
                            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $fPhone }}
                        </a>
                        @endif
                        @if($fEmail)
                        <a href="mailto:{{ $fEmail }}" class="flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
                            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $fEmail }}
                        </a>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Colonne 4 : Mon compte --}}
                <div>
                    <h4 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Mon compte</h4>
                    <ul class="space-y-2.5">
                        <li>
                            @auth
                            <a href="{{ route('account.dashboard') }}" wire:navigate class="text-sm text-slate-400 hover:text-white transition-colors">Mon tableau de bord</a>
                            @else
                            <a href="{{ route('login') }}" wire:navigate class="text-sm text-slate-400 hover:text-white transition-colors">Connexion</a>
                            @endauth
                        </li>
                        <li><a href="{{ route('account.orders') }}" wire:navigate class="text-sm text-slate-400 hover:text-white transition-colors">Mes commandes</a></li>
                        <li><a href="{{ route('account.wishlist.index') }}" wire:navigate class="text-sm text-slate-400 hover:text-white transition-colors">Liste de souhaits</a></li>
                        <li><a href="{{ route('about') }}" wire:navigate class="text-sm text-slate-400 hover:text-white transition-colors">À propos</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Conditions générales</a></li>
                    </ul>
                    @if($fAddress)
                    <div class="mt-5 flex items-start gap-2">
                        <svg class="w-4 h-4 text-slate-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="text-sm text-slate-400 leading-relaxed">{!! nl2br(e($fAddress)) !!}</span>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Barre bas --}}
    <div class="bg-slate-950">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-slate-500 text-center sm:text-left">
                    &copy; {{ date('Y') }} {{ $fSiteName }}. Tous droits réservés.
                </p>
                {{-- Paiements acceptés --}}
                <div class="flex items-center gap-2">
                    {{-- Visa --}}
                    <div class="h-7 px-2.5 bg-slate-800 border border-slate-700 rounded flex items-center justify-center" title="Visa">
                        <svg class="h-4 w-auto" viewBox="0 0 60 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <text x="0" y="16" font-family="Arial" font-weight="bold" font-size="16" fill="#1A1F71">VISA</text>
                        </svg>
                    </div>
                    {{-- Mastercard --}}
                    <div class="h-7 px-2 bg-slate-800 border border-slate-700 rounded flex items-center justify-center gap-0.5" title="Mastercard">
                        <div class="w-4 h-4 rounded-full bg-red-500 opacity-90"></div>
                        <div class="w-4 h-4 rounded-full bg-yellow-400 opacity-90 -ml-2"></div>
                    </div>
                    {{-- Wave --}}
                    <div class="h-7 px-2.5 bg-slate-800 border border-slate-700 rounded flex items-center justify-center" title="Wave">
                        <span class="text-[10px] font-bold text-blue-400 leading-none">Wave</span>
                    </div>
                    {{-- Orange Money --}}
                    <div class="h-7 px-2.5 bg-slate-800 border border-slate-700 rounded flex items-center gap-1 justify-center" title="Orange Money">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-500 shrink-0"></span>
                        <span class="text-[10px] font-medium text-slate-400 leading-none whitespace-nowrap">Orange</span>
                    </div>
                    {{-- MTN --}}
                    <div class="h-7 px-2.5 bg-slate-800 border border-slate-700 rounded flex items-center gap-1 justify-center" title="MTN MoMo">
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 shrink-0"></span>
                        <span class="text-[10px] font-medium text-slate-400 leading-none">MTN</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
