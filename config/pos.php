<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuration Imprimante POS
    |--------------------------------------------------------------------------
    |
    | Paramètres pour les imprimantes thermiques ESC/POS.
    | Compatible avec la plupart des imprimantes 80mm et 58mm.
    |
    */

    'enabled' => env('POS_PRINTER_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Type de connexion
    |--------------------------------------------------------------------------
    |
    | Types supportés: 'usb', 'network', 'bluetooth'
    | - usb: Imprimante locale via drivers Windows/Linux
    | - network: Imprimante réseau via IP (ex: 192.168.1.100)
    | - bluetooth: Via app mobile ou bridge (ex: Sunmi)
    |
    */

    'type' => env('POS_PRINTER_TYPE', 'usb'),

    /*
    |--------------------------------------------------------------------------
    | Nom / IP de l'imprimante
    |--------------------------------------------------------------------------
    |
    | Pour USB: Nom exact dans "Périphériques et imprimantes" (ex: "POS-80")
    | Pour Network: Adresse IP (ex: "192.168.1.100")
    |
    */

    'printer_name' => env('POS_PRINTER_NAME', 'POS-80'),

    /*
    |--------------------------------------------------------------------------
    | Port réseau (si type = network)
    |--------------------------------------------------------------------------
    |
    | Port TCP standard pour imprimantes ESC/POS: 9100
    |
    */

    'printer_port' => env('POS_PRINTER_PORT', 9100),

    /*
    |--------------------------------------------------------------------------
    | Largeur papier
    |--------------------------------------------------------------------------
    |
    | Nombre de caractères par ligne:
    | - 48: Papier 80mm (standard)
    | - 32: Papier 58mm (compact)
    |
    */

    'width' => env('POS_PRINTER_WIDTH', 48),

    /*
    |--------------------------------------------------------------------------
    | Impression automatique
    |--------------------------------------------------------------------------
    |
    | Imprimer automatiquement le reçu après chaque vente.
    |
    */

    'auto_print' => env('POS_AUTO_PRINT', false),

    /*
    |--------------------------------------------------------------------------
    | Coupe papier automatique
    |--------------------------------------------------------------------------
    |
    | Envoyer la commande de coupe après chaque impression.
    | Nécessite une imprimante avec guillotine intégrée.
    |
    */

    'auto_cut' => env('POS_AUTO_CUT', true),

    /*
    |--------------------------------------------------------------------------
    | Ouverture tiroir-caisse
    |--------------------------------------------------------------------------
    |
    | Ouvrir le tiroir-caisse après impression du reçu.
    | Nécessite un tiroir connecté en RJ11 à l'imprimante.
    |
    */

    'cash_drawer' => env('POS_CASH_DRAWER', false),

    /*
    |--------------------------------------------------------------------------
    | QR Code sur reçu
    |--------------------------------------------------------------------------
    |
    | Afficher un QR Code avec le lien de suivi de commande.
    | Toutes les imprimantes ne supportent pas cette fonction.
    |
    */

    'qrcode' => env('POS_QRCODE', true),

    /*
    |--------------------------------------------------------------------------
    | Logo boutique
    |--------------------------------------------------------------------------
    |
    | Chemin vers une image logo (format PNG/JPG).
    | L'image sera convertie en bitmap monochrome pour impression thermique.
    | null = pas de logo
    |
    */

    'logo_path' => env('POS_LOGO_PATH', null),

    /*
    |--------------------------------------------------------------------------
    | Encodage caractères
    |--------------------------------------------------------------------------
    |
    | Encodage utilisé par l'imprimante:
    | - CP437: Standard DOS (pas d'accents français)
    | - CP850: Western Europe (meilleur support français)
    | - UTF-8: Si imprimante moderne
    |
    */

    'encoding' => env('POS_ENCODING', 'CP850'),

];
