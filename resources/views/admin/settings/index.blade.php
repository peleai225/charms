@extends('layouts.admin')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres généraux')

@section('content')
<div class="space-y-6">
    <!-- Navigation -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-2 flex flex-wrap gap-2">
        <a href="{{ route('admin.settings.index') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-xl">Général</a>
        <a href="{{ route('admin.settings.shipping') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Livraison</a>
        <a href="{{ route('admin.settings.payment') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Paiement</a>
        <a href="{{ route('admin.settings.emails') }}" class="px-4 py-2 text-slate-700 hover:bg-slate-100 font-medium rounded-xl">Emails</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Informations boutique -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Informations de la boutique</h3>
                    <div class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nom du site *</label>
                                <input type="text" name="site_name" value="{{ $settings['site_name'] ?? config('app.name') }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email de contact *</label>
                                <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email admin (alertes stock)</label>
                                <input type="email" name="admin_email" value="{{ $settings['admin_email'] ?? $settings['contact_email'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="Par défaut : email de contact">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Description du site</label>
                            <textarea name="site_description" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-xl">{{ $settings['site_description'] ?? '' }}</textarea>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                                <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">WhatsApp</label>
                                <input type="text" name="social_whatsapp" value="{{ $settings['social_whatsapp'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="+225 XX XX XX XX XX">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Adresse</label>
                            <textarea name="contact_address" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-xl">{{ $settings['contact_address'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Devise et taxes</h3>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Devise *</label>
                            <select name="currency" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                                <option value="XOF" {{ ($settings['currency'] ?? 'XOF') === 'XOF' ? 'selected' : '' }}>XOF (Franc CFA)</option>
                                <option value="EUR" {{ ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                <option value="USD" {{ ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Symbole *</label>
                            <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? 'F CFA' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Taux de taxe (%)</label>
                            <input type="number" name="tax_rate" value="{{ $settings['tax_rate'] ?? 0 }}" step="0.01" min="0" max="100" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Caisse POS</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="pos_receipt_auto_print" value="0">
                            <input type="checkbox" name="pos_receipt_auto_print" value="1" {{ ($settings['pos_receipt_auto_print'] ?? '0') === '1' ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-slate-700">Ouvrir le reçu et lancer l'impression après validation de vente</span>
                        </label>
                        <p class="text-sm text-slate-500">Si activé, le reçu s'ouvre dans une nouvelle fenêtre et la boîte de dialogue d'impression du navigateur se lance. Configurez votre imprimante thermique comme imprimante par défaut pour un flux rapide.</p>
                        <a href="{{ route('admin.docs.caisse-pos-imprimante') }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 mt-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            Guide de configuration imprimante
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Réseaux sociaux</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Facebook</label>
                            <input type="url" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="https://facebook.com/...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Instagram</label>
                            <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="https://instagram.com/...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Twitter / X</label>
                            <input type="url" name="social_twitter" value="{{ $settings['social_twitter'] ?? '' }}" class="w-full px-4 py-2 border border-slate-300 rounded-xl" placeholder="https://twitter.com/...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Logo & Favicon</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Logo</label>
                            @if(!empty($settings['logo']))
                                <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo" class="h-16 mb-2">
                            @endif
                            <input type="file" name="logo" accept="image/*" class="w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Favicon</label>
                            @if(!empty($settings['favicon']))
                                <img src="{{ asset('storage/' . $settings['favicon']) }}" alt="Favicon" class="h-8 mb-2">
                            @endif
                            <input type="file" name="favicon" accept="image/*" class="w-full text-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Couleurs du thème</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Couleur principale</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? '#6366f1' }}" class="w-12 h-10 rounded border-0 cursor-pointer">
                                <input type="text" value="{{ $settings['primary_color'] ?? '#6366f1' }}" readonly class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm bg-slate-50" id="primary_color_text">
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Boutons, liens, éléments principaux</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Couleur secondaire</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="secondary_color" value="{{ $settings['secondary_color'] ?? '#8b5cf6' }}" class="w-12 h-10 rounded border-0 cursor-pointer">
                                <input type="text" value="{{ $settings['secondary_color'] ?? '#8b5cf6' }}" readonly class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm bg-slate-50" id="secondary_color_text">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Couleur d'accent</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="accent_color" value="{{ $settings['accent_color'] ?? '#f59e0b' }}" class="w-12 h-10 rounded border-0 cursor-pointer">
                                <input type="text" value="{{ $settings['accent_color'] ?? '#f59e0b' }}" readonly class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm bg-slate-50" id="accent_color_text">
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Promotions, alertes, badges</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Mode du thème</label>
                            <select name="theme_mode" class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                                <option value="light" {{ ($settings['theme_mode'] ?? 'light') === 'light' ? 'selected' : '' }}>Clair</option>
                                <option value="dark" {{ ($settings['theme_mode'] ?? '') === 'dark' ? 'selected' : '' }}>Sombre</option>
                                <option value="auto" {{ ($settings['theme_mode'] ?? '') === 'auto' ? 'selected' : '' }}>Auto (préférence système)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Pied de page</h3>
                    <textarea name="footer_text" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-xl text-sm" placeholder="Texte du pied de page...">{{ $settings['footer_text'] ?? '' }}</textarea>
                </div>

                <!-- Programme de fidélité -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">Programme de fidélité</h3>
                    <p class="text-sm text-slate-500 mb-4">Les points sont attribués automatiquement après chaque paiement confirmé.</p>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Points gagnés par 1 000 F CFA dépensés
                            </label>
                            <input type="number" name="loyalty_points_per_1000" min="0" max="1000"
                                   value="{{ $settings['loyalty_points_per_1000'] ?? 10 }}"
                                   class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                            <p class="text-xs text-slate-500 mt-1">Ex. : 10 pts × 5 000 F = 50 pts gagnés</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Valeur de 100 points (en F CFA)
                            </label>
                            <input type="number" name="loyalty_points_value" min="0"
                                   value="{{ $settings['loyalty_points_value'] ?? 500 }}"
                                   class="w-full px-4 py-2 border border-slate-300 rounded-xl">
                            <p class="text-xs text-slate-500 mt-1">Ex. : 100 pts = 500 F de réduction</p>
                        </div>
                    </div>
                </div>

                <!-- Tracking & Analytics -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">Tracking & Analytics</h3>
                    <p class="text-sm text-slate-500 mb-4">Laissez vide pour désactiver un pixel.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Google Analytics 4 — Measurement ID
                            </label>
                            <input type="text" name="ga4_id"
                                   value="{{ $settings['ga4_id'] ?? '' }}"
                                   placeholder="G-XXXXXXXXXX"
                                   class="w-full px-4 py-2 border border-slate-300 rounded-xl font-mono text-sm">
                            <p class="text-xs text-slate-500 mt-1">Trouvez cet ID dans Google Analytics → Admin → Flux de données</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Meta Pixel ID (Facebook / Instagram)
                            </label>
                            <input type="text" name="meta_pixel_id"
                                   value="{{ $settings['meta_pixel_id'] ?? '' }}"
                                   placeholder="123456789012345"
                                   class="w-full px-4 py-2 border border-slate-300 rounded-xl font-mono text-sm">
                            <p class="text-xs text-slate-500 mt-1">Gestionnaire de publicités Meta → Sources de données → Pixels</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                TikTok Pixel ID
                            </label>
                            <input type="text" name="tiktok_pixel_id"
                                   value="{{ $settings['tiktok_pixel_id'] ?? '' }}"
                                   placeholder="CXXXXXXXXXXXXXXXXXX"
                                   class="w-full px-4 py-2 border border-slate-300 rounded-xl font-mono text-sm">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Sync color picker with text input
    document.querySelectorAll('input[type="color"]').forEach(colorInput => {
        colorInput.addEventListener('input', function() {
            const textInput = document.getElementById(this.name + '_text');
            if (textInput) {
                textInput.value = this.value;
            }
        });
    });
</script>
@endpush
@endsection
