<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    /**
     * Page principale des paramètres
     */
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Mettre à jour les paramètres généraux
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'footer_text' => 'nullable|string|max:500',
            'social_facebook' => 'nullable|url',
            'social_instagram' => 'nullable|url',
            'social_twitter' => 'nullable|url',
            'social_whatsapp' => 'nullable|string|max:50',
            // Couleurs du thème
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'theme_mode' => 'nullable|in:light,dark,auto',
        ]);

        // Upload logo
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('settings', 'public');
            $this->setSetting('logo', $logoPath);
        }

        // Upload favicon
        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('settings', 'public');
            $this->setSetting('favicon', $faviconPath);
        }

        // Sauvegarder les autres paramètres
        foreach ($validated as $key => $value) {
            if (!in_array($key, ['logo', 'favicon'])) {
                $this->setSetting($key, $value);
            }
        }

        // Vider tous les caches pour application immédiate
        Setting::clearCache();

        return back()->with('success', 'Paramètres mis à jour et appliqués en temps réel.');
    }

    /**
     * Page paramètres de livraison
     */
    public function shipping()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.shipping', compact('settings'));
    }

    /**
     * Mettre à jour les paramètres de livraison
     */
    public function updateShipping(Request $request)
    {
        $validated = $request->validate([
            'shipping_enabled' => 'boolean',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'flat_rate_shipping' => 'nullable|numeric|min:0',
            'shipping_zones' => 'nullable|array',
            'shipping_zones.*.name' => 'required|string',
            'shipping_zones.*.cities' => 'required|string',
            'shipping_zones.*.price' => 'required|numeric|min:0',
        ]);

        $this->setSetting('shipping_enabled', $request->boolean('shipping_enabled') ? '1' : '0');
        $this->setSetting('free_shipping_threshold', $validated['free_shipping_threshold'] ?? null);
        $this->setSetting('flat_rate_shipping', $validated['flat_rate_shipping'] ?? null);
        
        if (isset($validated['shipping_zones'])) {
            $this->setSetting('shipping_zones', json_encode($validated['shipping_zones']));
        }

        // Vider tous les caches pour application immédiate
        Setting::clearCache();

        return back()->with('success', 'Paramètres de livraison mis à jour et appliqués en temps réel.');
    }

    /**
     * Page paramètres de paiement
     */
    public function payment()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.payment', compact('settings'));
    }

    /**
     * Mettre à jour les paramètres de paiement
     */
    public function updatePayment(Request $request)
    {
        $validated = $request->validate([
            'payment_cod_enabled' => 'boolean',
            'payment_cinetpay_enabled' => 'boolean',
            'payment_lygos_enabled' => 'boolean',
            'cinetpay_site_id' => 'nullable|string',
            'cinetpay_api_key' => 'nullable|string',
            'cinetpay_secret_key' => 'nullable|string',
            'cinetpay_mode' => 'nullable|in:sandbox,live',
            'lygos_api_key' => 'nullable|string',
        ]);

        $this->setSetting('payment_cod_enabled', $request->boolean('payment_cod_enabled') ? '1' : '0');
        $this->setSetting('payment_cinetpay_enabled', $request->boolean('payment_cinetpay_enabled') ? '1' : '0');
        $this->setSetting('payment_lygos_enabled', $request->boolean('payment_lygos_enabled') ? '1' : '0');
        
        if (!empty($validated['cinetpay_site_id'])) {
            $this->setSetting('cinetpay_site_id', $validated['cinetpay_site_id']);
        }
        if (!empty($validated['cinetpay_api_key'])) {
            $this->setSetting('cinetpay_api_key', $validated['cinetpay_api_key']);
        }
        if (!empty($validated['cinetpay_secret_key'])) {
            $this->setSetting('cinetpay_secret_key', $validated['cinetpay_secret_key']);
        }
        $this->setSetting('cinetpay_mode', $validated['cinetpay_mode'] ?? 'sandbox');

        // Lygos Pay
        if (!empty($validated['lygos_api_key'])) {
            $this->setSetting('lygos_api_key', $validated['lygos_api_key']);
            // Mettre à jour aussi le .env via config
            config(['lygos.api_key' => $validated['lygos_api_key']]);
        }

        // Vider tous les caches pour application immédiate
        Setting::clearCache();

        return back()->with('success', 'Paramètres de paiement mis à jour et appliqués en temps réel.');
    }

    /**
     * Page paramètres emails
     */
    public function emails()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings.emails', compact('settings'));
    }

    /**
     * Mettre à jour les paramètres emails
     */
    public function updateEmails(Request $request)
    {
        $validated = $request->validate([
            'mail_from_name' => 'required|string|max:255',
            'mail_from_address' => 'required|email',
            'mail_driver' => 'required|in:smtp,sendmail,mailgun',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:tls,ssl,null',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $this->setSetting($key, $value);
            }
        }

        // Vider tous les caches pour application immédiate
        Setting::clearCache();

        return back()->with('success', 'Paramètres email mis à jour et appliqués en temps réel.');
    }

    /**
     * Tester la connexion Lygos Pay
     */
    public function testLygosPay(Request $request)
    {
        try {
            $lygosService = new \App\Services\LygosPayService();
            $result = $lygosService->testConnection();
            
            if ($result['success']) {
                return back()->with('success', $result['message']);
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            \Log::error('Test Lygos Pay error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du test: ' . $e->getMessage());
        }
    }

    /**
     * Tester l'envoi d'un email
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Configurer la connexion mail depuis les paramètres
            \App\Services\MailConfigService::configureFromSettings();

            // Envoyer un email de test
            \Illuminate\Support\Facades\Mail::to($request->test_email)->send(new \App\Mail\TestEmail());

            return back()->with('success', "Email de test envoyé avec succès à {$request->test_email} !");
        } catch (\Exception $e) {
            \Log::error('Test email error: ' . $e->getMessage());
            
            $errorMessage = config('app.debug') 
                ? 'Erreur : ' . $e->getMessage() 
                : 'Erreur lors de l\'envoi de l\'email de test. Vérifiez votre configuration SMTP.';
            
            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Helper pour sauvegarder un paramètre (temps réel)
     */
    protected function setSetting(string $key, $value): void
    {
        // Utiliser Setting::set() qui vide automatiquement le cache
        Setting::set($key, $value);
    }
}

