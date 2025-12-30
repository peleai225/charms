<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CinetPay Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'intégration de CinetPay
    | Récupérez vos identifiants sur https://app.cinetpay.com
    |
    */

    // Identifiant du site (fourni par CinetPay)
    'site_id' => env('CINETPAY_SITE_ID', ''),

    // Clé API (fournie par CinetPay)
    'api_key' => env('CINETPAY_API_KEY', ''),

    // Clé secrète pour les webhooks
    'secret_key' => env('CINETPAY_SECRET_KEY', ''),

    // Mode sandbox pour les tests
    'sandbox' => env('CINETPAY_SANDBOX', true),

    // Devise par défaut
    'currency' => env('CINETPAY_CURRENCY', 'XOF'),

    // URLs de redirection
    'return_url' => env('CINETPAY_RETURN_URL', '/checkout/confirmation'),
    'cancel_url' => env('CINETPAY_CANCEL_URL', '/checkout/annulation'),
    'notify_url' => env('CINETPAY_NOTIFY_URL', '/webhook/cinetpay'),

    // URLs de l'API CinetPay
    'api_base_url' => 'https://api-checkout.cinetpay.com/v2',
    'payment_url' => 'https://api-checkout.cinetpay.com/v2/payment',
    'check_url' => 'https://api-checkout.cinetpay.com/v2/payment/check',

    // Canaux de paiement disponibles
    'channels' => env('CINETPAY_CHANNELS', 'ALL'),
    // Options: ALL, MOBILE_MONEY, CREDIT_CARD, WALLET
];

