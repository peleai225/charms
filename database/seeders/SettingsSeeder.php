<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Général
            ['key' => 'site_name', 'value' => 'Chamse E-Commerce', 'type' => 'text', 'group' => 'general', 'label' => 'Nom du site', 'is_public' => true],
            ['key' => 'site_description', 'value' => 'Votre boutique en ligne de confiance', 'type' => 'textarea', 'group' => 'general', 'label' => 'Description du site', 'is_public' => true],
            ['key' => 'contact_email', 'value' => 'contact@chamse.fr', 'type' => 'text', 'group' => 'general', 'label' => 'Email de contact', 'is_public' => true],
            ['key' => 'contact_phone', 'value' => '+33 1 23 45 67 89', 'type' => 'text', 'group' => 'general', 'label' => 'Téléphone', 'is_public' => true],
            ['key' => 'contact_address', 'value' => '123 Rue du Commerce, 75001 Paris', 'type' => 'textarea', 'group' => 'general', 'label' => 'Adresse', 'is_public' => true],
            
            // Boutique
            ['key' => 'currency', 'value' => 'EUR', 'type' => 'text', 'group' => 'store', 'label' => 'Devise', 'is_public' => true],
            ['key' => 'currency_symbol', 'value' => '€', 'type' => 'text', 'group' => 'store', 'label' => 'Symbole devise', 'is_public' => true],
            ['key' => 'default_tax_rate', 'value' => '20', 'type' => 'number', 'group' => 'store', 'label' => 'Taux de TVA par défaut'],
            ['key' => 'free_shipping_threshold', 'value' => '49', 'type' => 'number', 'group' => 'store', 'label' => 'Seuil livraison gratuite', 'is_public' => true],
            ['key' => 'low_stock_threshold', 'value' => '5', 'type' => 'number', 'group' => 'store', 'label' => 'Seuil alerte stock bas'],
            ['key' => 'products_per_page', 'value' => '12', 'type' => 'number', 'group' => 'store', 'label' => 'Produits par page'],
            
            // Apparence
            ['key' => 'primary_color', 'value' => '#2563eb', 'type' => 'color', 'group' => 'appearance', 'label' => 'Couleur primaire', 'is_public' => true],
            ['key' => 'secondary_color', 'value' => '#64748b', 'type' => 'color', 'group' => 'appearance', 'label' => 'Couleur secondaire', 'is_public' => true],
            ['key' => 'accent_color', 'value' => '#f59e0b', 'type' => 'color', 'group' => 'appearance', 'label' => 'Couleur accent', 'is_public' => true],
            ['key' => 'logo', 'value' => null, 'type' => 'image', 'group' => 'appearance', 'label' => 'Logo', 'is_public' => true],
            ['key' => 'favicon', 'value' => null, 'type' => 'image', 'group' => 'appearance', 'label' => 'Favicon', 'is_public' => true],
            
            // Réseaux sociaux
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/chamse', 'type' => 'text', 'group' => 'social', 'label' => 'Facebook', 'is_public' => true],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com/chamse', 'type' => 'text', 'group' => 'social', 'label' => 'Instagram', 'is_public' => true],
            ['key' => 'twitter_url', 'value' => 'https://twitter.com/chamse', 'type' => 'text', 'group' => 'social', 'label' => 'Twitter', 'is_public' => true],
            
            // Email
            ['key' => 'mail_from_name', 'value' => 'Chamse E-Commerce', 'type' => 'text', 'group' => 'email', 'label' => 'Nom expéditeur'],
            ['key' => 'mail_from_address', 'value' => 'noreply@chamse.fr', 'type' => 'text', 'group' => 'email', 'label' => 'Email expéditeur'],
            
            // Commandes
            ['key' => 'order_prefix', 'value' => 'CMD', 'type' => 'text', 'group' => 'orders', 'label' => 'Préfixe commandes'],
            ['key' => 'invoice_prefix', 'value' => 'FAC', 'type' => 'text', 'group' => 'orders', 'label' => 'Préfixe factures'],
            ['key' => 'enable_guest_checkout', 'value' => '1', 'type' => 'boolean', 'group' => 'orders', 'label' => 'Commande sans compte'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

