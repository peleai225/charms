<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Lygos Pay Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'intégration de Lygos Pay
    | Récupérez votre clé API sur https://dashboard.lygosapp.com
    |
    */

    // Clé API (fournie par Lygos Pay)
    'api_key' => env('LYGOS_API_KEY', ''),

    // URL de base de l'API
    'api_base_url' => env('LYGOS_API_BASE_URL', 'https://api.lygosapp.com/v1'),

    // Devise par défaut
    'currency' => env('LYGOS_CURRENCY', 'XOF'),

    // URLs de redirection
    'return_url' => env('LYGOS_RETURN_URL', '/checkout/confirmation'),
    'cancel_url' => env('LYGOS_CANCEL_URL', '/checkout/annulation'),
    'notify_url' => env('LYGOS_NOTIFY_URL', '/webhook/lygos'),
];

