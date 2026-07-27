{{-- Contenu unifié des paramètres — inclus par index/shipping/payment/emails avec $defaultTab --}}
@php $defaultTab = $defaultTab ?? 'general'; @endphp

<div class="p-4 sm:p-6 space-y-5" x-data="settingsTabs('{{ $defaultTab }}')">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-[13px]">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-[13px]">{{ session('error') }}</div>
    @endif

    {{-- Tab nav --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-1.5 flex flex-wrap gap-1">
        <button type="button" @click="tab = 'general'"
            :class="tab === 'general' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
            class="px-4 py-2 font-semibold text-[13px] rounded-lg transition-all">Général</button>
        <button type="button" @click="tab = 'shipping'"
            :class="tab === 'shipping' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
            class="px-4 py-2 font-semibold text-[13px] rounded-lg transition-all">Livraison</button>
        <button type="button" @click="tab = 'payment'"
            :class="tab === 'payment' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
            class="px-4 py-2 font-semibold text-[13px] rounded-lg transition-all">Paiement</button>
        <button type="button" @click="tab = 'emails'"
            :class="tab === 'emails' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
            class="px-4 py-2 font-semibold text-[13px] rounded-lg transition-all">Emails</button>
    </div>

    {{-- ════════════════════════════════════════════════════
         ONGLET : GÉNÉRAL
    ════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'general'" x-cloak>
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div class="grid lg:grid-cols-3 gap-5">

                {{-- Main column --}}
                <div class="lg:col-span-2 space-y-5">

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Informations de la boutique</h3>
                        <div class="space-y-4">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-medium text-gray-700 mb-1">Nom du site *</label>
                                    <input type="text" name="site_name" value="{{ $settings['site_name'] ?? config('app.name') }}" required
                                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-[13px] font-medium text-gray-700 mb-1">Email de contact *</label>
                                    <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" required
                                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-[13px] font-medium text-gray-700 mb-1">Email admin (alertes stock)</label>
                                    <input type="email" name="admin_email" value="{{ $settings['admin_email'] ?? $settings['contact_email'] ?? '' }}"
                                        placeholder="Par défaut : email de contact"
                                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Description du site</label>
                                <textarea name="site_description" rows="2"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ $settings['site_description'] ?? '' }}</textarea>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-medium text-gray-700 mb-1">Téléphone</label>
                                    <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}"
                                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-[13px] font-medium text-gray-700 mb-1">WhatsApp</label>
                                    <input type="text" name="social_whatsapp" value="{{ $settings['social_whatsapp'] ?? '' }}"
                                        placeholder="+225 XX XX XX XX XX"
                                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Adresse</label>
                                <textarea name="contact_address" rows="2"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ $settings['contact_address'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Devise et taxes</h3>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Devise *</label>
                                <select name="currency" class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="XOF" {{ ($settings['currency'] ?? 'XOF') === 'XOF' ? 'selected' : '' }}>XOF (Franc CFA)</option>
                                    <option value="EUR" {{ ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                    <option value="USD" {{ ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Symbole *</label>
                                <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? 'F CFA' }}" required
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Taux de taxe (%)</label>
                                <input type="number" name="tax_rate" value="{{ $settings['tax_rate'] ?? 0 }}" step="0.01" min="0" max="100"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-[14px] font-semibold text-gray-900 mb-3">Caisse POS</h3>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="pos_receipt_auto_print" value="0">
                                <input type="checkbox" name="pos_receipt_auto_print" value="1"
                                    {{ ($settings['pos_receipt_auto_print'] ?? '0') === '1' ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-[13px] text-gray-700">Ouvrir le reçu et lancer l'impression après validation de vente</span>
                            </label>
                            <p class="text-[12px] text-gray-500">Si activé, le reçu s'ouvre dans une nouvelle fenêtre et la boîte de dialogue d'impression se lance. Configurez votre imprimante thermique comme imprimante par défaut.</p>
                            <a href="{{ route('admin.docs.caisse-pos-imprimante') }}" target="_blank" class="inline-flex items-center gap-1.5 text-[13px] text-blue-600 hover:text-blue-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                Guide de configuration imprimante
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Réseaux sociaux</h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            @foreach([
                                ['name' => 'social_facebook',  'label' => 'Facebook',    'placeholder' => 'https://facebook.com/...'],
                                ['name' => 'social_instagram', 'label' => 'Instagram',   'placeholder' => 'https://instagram.com/...'],
                                ['name' => 'social_twitter',   'label' => 'Twitter / X', 'placeholder' => 'https://x.com/...'],
                                ['name' => 'social_tiktok',    'label' => 'TikTok',      'placeholder' => 'https://tiktok.com/@...'],
                            ] as $social)
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">{{ $social['label'] }}</label>
                                <input type="url" name="{{ $social['name'] }}" value="{{ $settings[$social['name']] ?? '' }}"
                                    placeholder="{{ $social['placeholder'] }}"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-5">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Logo & Favicon</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-2">Logo</label>
                                @if(!empty($settings['logo']))
                                    <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo" class="h-12 mb-2 rounded">
                                @endif
                                <input type="file" name="logo" accept="image/*" class="w-full text-[13px] text-gray-600">
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-2">Favicon</label>
                                @if(!empty($settings['favicon']))
                                    <img src="{{ asset('storage/' . $settings['favicon']) }}" alt="Favicon" class="h-8 mb-2">
                                @endif
                                <input type="file" name="favicon" accept="image/*" class="w-full text-[13px] text-gray-600">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Couleurs du thème</h3>
                        <div class="space-y-4">
                            @foreach([
                                ['name' => 'primary_color',   'label' => 'Principale',   'default' => '#6366f1', 'hint' => 'Boutons, liens, éléments principaux'],
                                ['name' => 'secondary_color', 'label' => 'Secondaire',   'default' => '#8b5cf6', 'hint' => ''],
                                ['name' => 'accent_color',    'label' => "D'accent",     'default' => '#f59e0b', 'hint' => 'Promotions, alertes, badges'],
                            ] as $color)
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-2">{{ $color['label'] }}</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="{{ $color['name'] }}" value="{{ $settings[$color['name']] ?? $color['default'] }}"
                                        class="w-10 h-9 rounded border border-gray-200 cursor-pointer">
                                    <input type="text" value="{{ $settings[$color['name']] ?? $color['default'] }}" readonly
                                        id="{{ $color['name'] }}_text"
                                        class="flex-1 h-9 px-3 border border-gray-200 rounded-lg text-[13px] bg-gray-50 font-mono">
                                </div>
                                @if($color['hint'])
                                    <p class="text-[11px] text-gray-400 mt-1">{{ $color['hint'] }}</p>
                                @endif
                            </div>
                            @endforeach
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-2">Mode du thème</label>
                                <select name="theme_mode" class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="light" {{ ($settings['theme_mode'] ?? 'light') === 'light' ? 'selected' : '' }}>Clair</option>
                                    <option value="dark"  {{ ($settings['theme_mode'] ?? '') === 'dark'  ? 'selected' : '' }}>Sombre</option>
                                    <option value="auto"  {{ ($settings['theme_mode'] ?? '') === 'auto'  ? 'selected' : '' }}>Auto (préférence système)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-[14px] font-semibold text-gray-900 mb-3">Pied de page</h3>
                        <textarea name="footer_text" rows="3" placeholder="Texte du pied de page..."
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ $settings['footer_text'] ?? '' }}</textarea>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-[14px] font-semibold text-gray-900 mb-1">Programme de fidélité</h3>
                        <p class="text-[12px] text-gray-500 mb-4">Points attribués automatiquement après chaque paiement confirmé.</p>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Points par 1 000 F CFA</label>
                                <input type="number" name="loyalty_points_per_1000" min="0" max="1000"
                                    value="{{ $settings['loyalty_points_per_1000'] ?? 10 }}"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-[11px] text-gray-400 mt-1">Ex. : 10 pts × 5 000 F = 50 pts</p>
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Valeur de 100 points (F CFA)</label>
                                <input type="number" name="loyalty_points_value" min="0"
                                    value="{{ $settings['loyalty_points_value'] ?? 500 }}"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-[11px] text-gray-400 mt-1">Ex. : 100 pts = 500 F de réduction</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-[14px] font-semibold text-gray-900 mb-1">Tracking & Analytics</h3>
                        <p class="text-[12px] text-gray-500 mb-4">Laissez vide pour désactiver un pixel.</p>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Google Analytics 4 — Measurement ID</label>
                                <input type="text" name="ga4_id" value="{{ $settings['ga4_id'] ?? '' }}" placeholder="G-XXXXXXXXXX"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Meta Pixel ID</label>
                                <input type="text" name="meta_pixel_id" value="{{ $settings['meta_pixel_id'] ?? '' }}" placeholder="123456789012345"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">TikTok Pixel ID</label>
                                <input type="text" name="tiktok_pixel_id" value="{{ $settings['tiktok_pixel_id'] ?? '' }}" placeholder="CXXXXXXXXXXXXXXXXXX"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full h-10 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[13px] rounded-lg transition-colors">
                        Enregistrer les modifications
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ════════════════════════════════════════════════════
         ONGLET : LIVRAISON
    ════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'shipping'" x-cloak>
        <form method="POST" action="{{ route('admin.settings.shipping.update') }}" class="space-y-5">
            @csrf

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Options de livraison</h3>
                <div class="space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="shipping_enabled" value="1"
                            {{ ($settings['shipping_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 rounded border-gray-300">
                        <span class="text-[13px] text-gray-700">Activer la livraison</span>
                    </label>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Livraison gratuite à partir de</label>
                            <div class="relative">
                                <input type="number" name="free_shipping_threshold" value="{{ $settings['free_shipping_threshold'] ?? '' }}"
                                    step="100" min="0" placeholder="Ex: 50000"
                                    class="w-full h-9 pl-3 pr-16 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[12px] text-gray-400">F CFA</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Tarif forfaitaire</label>
                            <div class="relative">
                                <input type="number" name="flat_rate_shipping" value="{{ $settings['flat_rate_shipping'] ?? '' }}"
                                    step="100" min="0" placeholder="Ex: 2000"
                                    class="w-full h-9 pl-3 pr-16 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[12px] text-gray-400">F CFA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[14px] font-semibold text-gray-900">Zones de livraison</h3>
                    <button type="button" onclick="addShippingZone()"
                        class="h-7 px-3 bg-blue-600 text-white text-[12px] font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        + Ajouter
                    </button>
                </div>
                <div id="zones-container" class="space-y-3">
                    @php $zones = json_decode($settings['shipping_zones'] ?? '[]', true) ?: []; @endphp
                    @forelse($zones as $index => $zone)
                    <div class="zone-item p-4 border border-gray-200 rounded-xl">
                        <div class="grid md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[12px] font-medium text-gray-700 mb-1">Nom de la zone</label>
                                <input type="text" name="shipping_zones[{{ $index }}][name]" value="{{ $zone['name'] }}"
                                    placeholder="Ex: Abidjan"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-[12px] font-medium text-gray-700 mb-1">Villes (séparées par virgule)</label>
                                <input type="text" name="shipping_zones[{{ $index }}][cities]" value="{{ $zone['cities'] }}"
                                    placeholder="Ex: Cocody, Plateau"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <label class="block text-[12px] font-medium text-gray-700 mb-1">Prix (F CFA)</label>
                                    <input type="number" name="shipping_zones[{{ $index }}][price]" value="{{ $zone['price'] }}"
                                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <button type="button" onclick="this.closest('.zone-item').remove()"
                                    class="self-end h-9 w-9 flex items-center justify-center text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-[13px] text-gray-400">Aucune zone configurée. Utilisez le tarif forfaitaire ou ajoutez des zones.</p>
                    @endforelse
                </div>
            </div>

            <button type="submit" class="h-10 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[13px] rounded-lg transition-colors">
                Enregistrer
            </button>
        </form>
    </div>

    {{-- ════════════════════════════════════════════════════
         ONGLET : PAIEMENT
    ════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'payment'" x-cloak>
        <div class="space-y-5">
            <form method="POST" action="{{ route('admin.settings.payment.update') }}" class="space-y-5">
                @csrf

                {{-- Paiement à la livraison --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-[14px] font-semibold text-gray-900">Paiement à la livraison</h3>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="payment_cod_enabled" value="1"
                                {{ ($settings['payment_cod_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <p class="text-[13px] text-gray-500">Permettre aux clients de payer en espèces à la réception de leur commande.</p>
                </div>

                {{-- MoneyFusion --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-[11px]">MF</span>
                            </div>
                            <h3 class="text-[14px] font-semibold text-gray-900">MoneyFusion</h3>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="payment_moneyfusion_enabled" value="1"
                                {{ ($settings['payment_moneyfusion_enabled'] ?? '0') === '1' ? 'checked' : '' }}
                                class="sr-only peer" id="moneyfusionToggle">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                    </div>
                    <p class="text-[13px] text-gray-500 mb-4">Solution de paiement mobile money (Orange Money, MTN, Wave, Moov) pour l'Afrique.</p>

                    <div id="moneyfusionSettings" class="space-y-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">URL API</label>
                            <input type="text" name="moneyfusion_api_url" value="{{ $settings['moneyfusion_api_url'] ?? '' }}"
                                placeholder="https://..."
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-[12px] text-gray-400">Récupérez votre URL API sur votre tableau de bord MoneyFusion.</p>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Clé API (optionnel)</label>
                            <input type="password" name="moneyfusion_api_key" value="{{ $settings['moneyfusion_api_key'] ?? '' }}"
                                placeholder="Votre clé API MoneyFusion"
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="p-4 bg-green-50 border border-green-100 rounded-xl text-[13px] text-green-800 space-y-1.5">
                            <p><strong>URL de Webhook :</strong><br>
                                <code class="bg-green-100 px-1.5 py-0.5 rounded text-[12px]">{{ route('webhook.moneyfusion') }}</code>
                            </p>
                            <p><strong>URL de retour :</strong> Le client sera redirigé automatiquement après le paiement.</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="h-10 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[13px] rounded-lg transition-colors">
                    Enregistrer
                </button>
            </form>

            {{-- Pusher --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 class="text-[14px] font-semibold text-gray-900">Notifications temps réel (Pusher)</h3>
                </div>
                <p class="text-[13px] text-gray-600 mb-4">Configurez Pusher pour recevoir des notifications en direct (nouvelle commande, son) sans recharger le backoffice.</p>
                <div class="p-4 bg-gray-50 border border-gray-100 rounded-xl">
                    <p class="text-[13px] text-gray-700 mb-2">Ajoutez ces variables dans votre fichier <code class="bg-gray-200 px-1 rounded text-[12px]">.env</code> :</p>
                    <pre class="text-[11px] bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">PUSHER_APP_ID=votre_app_id
PUSHER_APP_KEY=votre_cle
PUSHER_APP_SECRET=votre_secret
PUSHER_APP_CLUSTER=mt1
BROADCAST_CONNECTION=pusher

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"</pre>
                    <p class="text-[12px] text-gray-400 mt-2">Créez une app gratuite sur <a href="https://dashboard.pusher.com" target="_blank" class="text-blue-600 hover:underline">dashboard.pusher.com</a></p>
                    <p class="text-[12px] text-amber-600 mt-1"><strong>Important :</strong> Lancez <code class="bg-amber-100 px-1 rounded text-[11px]">php artisan queue:work</code> pour diffuser les notifications.</p>
                </div>
            </div>

            {{-- Test connexions --}}
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                <h4 class="text-[13px] font-medium text-gray-900 mb-1">Tester les connexions API</h4>
                <p class="text-[12px] text-gray-500 mb-3">Enregistrez d'abord vos paramètres ci-dessus, puis testez les connexions.</p>
                <form method="POST" action="{{ route('admin.settings.payment.test-moneyfusion') }}" class="inline">
                    @csrf
                    <button type="submit" class="h-9 px-4 bg-green-600 text-white font-medium text-[13px] rounded-lg hover:bg-green-700 transition-colors">
                        Tester MoneyFusion
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         ONGLET : EMAILS
    ════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'emails'" x-cloak>
        <div class="space-y-5">
            <form method="POST" action="{{ route('admin.settings.emails.update') }}" class="space-y-5">
                @csrf

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Expéditeur</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Nom d'expéditeur *</label>
                            <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] ?? config('app.name') }}" required
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Email d'expéditeur *</label>
                            <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] ?? '' }}" required
                                class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Configuration SMTP</h3>
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-100 rounded-xl text-[13px] text-blue-800">
                        <p class="font-medium mb-1">Configuration Gmail</p>
                        <p class="text-[12px]">
                            Serveur : <code class="bg-blue-100 px-1 rounded">smtp.gmail.com</code> · Port <code class="bg-blue-100 px-1 rounded">587</code> (TLS) ou <code class="bg-blue-100 px-1 rounded">465</code> (SSL)<br>
                            <strong>Important :</strong> Utilisez un <strong>mot de passe d'application</strong> Gmail (pas votre mot de passe normal).
                        </p>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Driver</label>
                            <select name="mail_driver" class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="smtp"     {{ ($settings['mail_driver'] ?? 'smtp') === 'smtp'     ? 'selected' : '' }}>SMTP</option>
                                <option value="sendmail" {{ ($settings['mail_driver'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="mailgun"  {{ ($settings['mail_driver'] ?? '') === 'mailgun'  ? 'selected' : '' }}>Mailgun</option>
                            </select>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Serveur SMTP</label>
                                <input type="text" name="mail_host" value="{{ $settings['mail_host'] ?? '' }}" placeholder="smtp.gmail.com"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Port</label>
                                <input type="number" name="mail_port" value="{{ $settings['mail_port'] ?? 587 }}"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-[11px] text-gray-400 mt-1">587 (TLS) ou 465 (SSL) pour Gmail</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
                                <input type="text" name="mail_username" value="{{ $settings['mail_username'] ?? '' }}"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-[13px] font-medium text-gray-700 mb-1">Mot de passe</label>
                                <input type="password" name="mail_password" value="{{ $settings['mail_password'] ?? '' }}" placeholder="••••••••"
                                    class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-medium text-gray-700 mb-1">Chiffrement</label>
                            <select name="mail_encryption" class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="tls"  {{ ($settings['mail_encryption'] ?? 'tls') === 'tls'  ? 'selected' : '' }}>TLS (Recommandé pour Gmail)</option>
                                <option value="ssl"  {{ ($settings['mail_encryption'] ?? '') === 'ssl'  ? 'selected' : '' }}>SSL</option>
                                <option value="null" {{ ($settings['mail_encryption'] ?? '') === 'null' ? 'selected' : '' }}>Aucun</option>
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">TLS pour port 587, SSL pour port 465</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="h-10 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[13px] rounded-lg transition-colors">
                    Enregistrer
                </button>
            </form>

            {{-- Test email --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Tester la configuration</h3>
                <form method="POST" action="{{ route('admin.settings.emails.test') }}" class="flex gap-3">
                    @csrf
                    <input type="email" name="test_email" value="{{ $settings['mail_from_address'] ?? auth()->user()->email }}"
                        placeholder="Email de test" required
                        class="flex-1 h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button type="submit" class="h-9 px-4 bg-green-600 text-white font-medium text-[13px] rounded-lg hover:bg-green-700 transition-colors">
                        Envoyer un test
                    </button>
                </form>
                <p class="text-[12px] text-gray-400 mt-2">Un email de test sera envoyé pour vérifier que la configuration fonctionne.</p>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function settingsTabs(defaultTab) {
    return {
        tab: defaultTab,
    };
}

// Sync color picker → text input
const initColorSync = () => {
    document.querySelectorAll('input[type="color"]').forEach(colorInput => {
        colorInput.removeEventListener('input', colorInput._colorSyncHandler);
        colorInput._colorSyncHandler = function() {
            const textInput = document.getElementById(this.name + '_text');
            if (textInput) textInput.value = this.value;
        };
        colorInput.addEventListener('input', colorInput._colorSyncHandler);
    });
};
initColorSync();
document.addEventListener('livewire:navigated', initColorSync);

// Shipping zones dynamic add
window._shippingZoneIndex = {{ count($zones ?? []) }};
function addShippingZone() {
    const container = document.getElementById('zones-container');
    const i = window._shippingZoneIndex++;
    container.insertAdjacentHTML('beforeend', `
        <div class="zone-item p-4 border border-gray-200 rounded-xl">
            <div class="grid md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[12px] font-medium text-gray-700 mb-1">Nom de la zone</label>
                    <input type="text" name="shipping_zones[${i}][name]" placeholder="Ex: Abidjan"
                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-gray-700 mb-1">Villes (séparées par virgule)</label>
                    <input type="text" name="shipping_zones[${i}][cities]" placeholder="Ex: Cocody, Plateau"
                        class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex gap-2">
                    <div class="flex-1">
                        <label class="block text-[12px] font-medium text-gray-700 mb-1">Prix (F CFA)</label>
                        <input type="number" name="shipping_zones[${i}][price]"
                            class="w-full h-9 px-3 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="button" onclick="this.closest('.zone-item').remove()"
                        class="self-end h-9 w-9 flex items-center justify-center text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
    `);
}

// MoneyFusion toggle
const initMoneyfusionToggle = () => {
    const toggle = document.getElementById('moneyfusionToggle');
    const settings = document.getElementById('moneyfusionSettings');
    if (toggle && settings) {
        toggle.removeEventListener('change', toggle._mfHandler);
        toggle._mfHandler = function() { settings.style.display = this.checked ? 'block' : 'none'; };
        toggle.addEventListener('change', toggle._mfHandler);
        settings.style.display = toggle.checked ? 'block' : 'none';
    }
};
initMoneyfusionToggle();
document.addEventListener('livewire:navigated', initMoneyfusionToggle);
</script>
@endpush
