<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Page principale des paramètres
     */
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Settings/Index', [
            'settings'  => $settings,
            'activeTab' => 'general',
        ]);
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
            'admin_email' => 'nullable|email',
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
            'social_tiktok' => 'nullable|url',
            // Couleurs du thème
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'theme_mode' => 'nullable|in:light,dark,auto',
            'pos_receipt_auto_print' => 'boolean',
            // Fidélité
            'loyalty_points_per_1000' => 'nullable|integer|min:0|max:1000',
            'loyalty_points_value'    => 'nullable|integer|min:0',
            // Tracking & Analytics
            'ga4_id'          => 'nullable|string|max:30',
            'meta_pixel_id'   => 'nullable|string|max:30',
            'tiktok_pixel_id' => 'nullable|string|max:30',
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

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Settings/Index', [
            'settings'  => $settings,
            'activeTab' => 'shipping',
        ]);
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

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Settings/Index', [
            'settings'  => $settings,
            'activeTab' => 'payment',
        ]);
    }

    /**
     * Mettre à jour les paramètres de paiement
     */
    public function updatePayment(Request $request)
    {
        $validated = $request->validate([
            'payment_cod_enabled'         => 'boolean',
            'payment_moneyfusion_enabled' => 'boolean',
            'moneyfusion_api_url'         => 'nullable|url',
            'moneyfusion_api_key'         => 'nullable|string',
            'payment_jeko_enabled'        => 'boolean',
            'jeko_api_key'                => 'nullable|string|max:255',
            'jeko_api_key_id'             => 'nullable|string|max:255',
            'jeko_store_id'               => 'nullable|string|max:255',
            'jeko_webhook_secret'         => 'nullable|string|max:255',
        ]);

        $this->setSetting('payment_cod_enabled', $request->boolean('payment_cod_enabled') ? '1' : '0');
        $this->setSetting('payment_moneyfusion_enabled', $request->boolean('payment_moneyfusion_enabled') ? '1' : '0');
        $this->setSetting('payment_jeko_enabled', $request->boolean('payment_jeko_enabled') ? '1' : '0');

        if (!empty($validated['moneyfusion_api_url'])) {
            $this->setSetting('moneyfusion_api_url', $validated['moneyfusion_api_url']);
        }
        if (!empty($validated['moneyfusion_api_key'])) {
            $this->setSetting('moneyfusion_api_key', $validated['moneyfusion_api_key']);
        }

        foreach (['jeko_api_key', 'jeko_api_key_id', 'jeko_store_id', 'jeko_webhook_secret'] as $key) {
            if (!empty($validated[$key])) {
                $this->setSetting($key, $validated[$key]);
            }
        }

        Setting::clearCache();

        return back()->with('success', 'Paramètres de paiement mis à jour.');
    }

    /**
     * Page paramètres emails
     */
    public function emails()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Settings/Index', [
            'settings'  => $settings,
            'activeTab' => 'emails',
        ]);
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

    /**
     * Tester la connexion Jeko Africa
     */
    public function testJeko(Request $request)
    {
        $request->validate([
            'api_key'    => 'required|string',
            'api_key_id' => 'required|string',
            'store_id'   => 'nullable|string',
        ]);

        // Injecter les valeurs reçues dans le service pour tester avant sauvegarde
        \App\Models\Setting::set('jeko_api_key', $request->api_key);
        \App\Models\Setting::set('jeko_api_key_id', $request->api_key_id);
        if ($request->store_id) {
            \App\Models\Setting::set('jeko_store_id', $request->store_id);
        }
        \App\Models\Setting::clearCache();

        $service = new \App\Services\JekoAfricaService();
        $result  = $service->testConnection();

        return response()->json($result);
    }

    /**
     * Tester la connexion MoneyFusion
     */
    public function testMoneyFusion(Request $request)
    {
        try {
            $service = new \App\Services\MoneyFusionService();
            $result = $service->testConnection();

            if ($result['success']) {
                return back()->with('success', $result['message']);
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            \Log::error('Test MoneyFusion error: ' . $e->getMessage());
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
     * Page paramètres POS / impression
     */
    public function pos()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        Inertia::setRootView('layouts.admin-inertia');
        return Inertia::render('Admin/Settings/Index', [
            'settings'  => $settings,
            'activeTab' => 'pos',
        ]);
    }

    /**
     * Mettre à jour les paramètres POS / impression thermique
     */
    public function updatePos(Request $request)
    {
        $request->validate([
            'pos_printer_enabled'    => 'boolean',
            'pos_printer_type'       => 'nullable|in:network,usb',
            'pos_printer_ip'         => 'nullable|string|max:255',
            'pos_printer_port'       => 'nullable|integer|min:1|max:65535',
            'pos_printer_width'      => 'nullable|in:48,32',
            'pos_receipt_auto_print' => 'boolean',
            'pos_auto_cut'           => 'boolean',
            'pos_cash_drawer'        => 'boolean',
        ]);

        $booleans = ['pos_printer_enabled', 'pos_receipt_auto_print', 'pos_auto_cut', 'pos_cash_drawer'];
        foreach ($booleans as $key) {
            $this->setSetting($key, $request->boolean($key) ? '1' : '0');
        }

        foreach (['pos_printer_type', 'pos_printer_ip', 'pos_printer_port', 'pos_printer_width'] as $key) {
            if ($request->has($key)) {
                $this->setSetting($key, $request->input($key));
            }
        }

        Setting::clearCache();

        return back()->with('success', 'Paramètres d\'impression mis à jour.');
    }

    /**
     * Tester la connexion à l'imprimante réseau
     */
    public function testPrinter(Request $request)
    {
        $request->validate([
            'type' => 'required|in:network,usb',
            'ip'   => 'required|string|max:255',
            'port' => 'nullable|integer',
        ]);

        if ($request->type === 'network') {
            $ip   = $request->ip;
            $port = (int) ($request->port ?? 9100);

            $connection = @fsockopen($ip, $port, $errno, $errstr, 3);
            if ($connection) {
                fclose($connection);
                return response()->json(['success' => true, 'message' => "Imprimante joignable à {$ip}:{$port}"]);
            }
            return response()->json(['success' => false, 'message' => "Impossible de joindre {$ip}:{$port} — {$errstr}"]);
        }

        // USB : on vérifie juste que le nom est renseigné
        return response()->json(['success' => true, 'message' => 'Configuration USB enregistrée (test réseau non applicable)']);
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

