<?php
/**
 * index.php adapté pour : chamse/ dans public_html + contenu de public/ à la racine
 *
 * Structure sur le serveur :
 *   public_html/
 *     chamse/          ← dossier Laravel (app/, bootstrap/, vendor/, etc.)
 *     index.php        ← ce fichier (renommer en index.php)
 *     .htaccess
 *     build/
 *
 * Copier ce fichier en index.php dans public_html et supprimer le .php du nom.
 */
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Chemin vers la racine Laravel (dossier chamse)
$laravelRoot = __DIR__ . '/chamse';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelRoot . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelRoot . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelRoot . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
