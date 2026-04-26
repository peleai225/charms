<?php
/**
 * SCRIPT D'URGENCE — Vide les caches Laravel sans dépendre des routes.
 *
 * À utiliser quand le cache de routes (bootstrap/cache/routes-v7.php) est figé
 * après un git pull et empêche les nouvelles routes d'être reconnues.
 *
 * USAGE :
 *   https://votre-domaine.com/__clear-cache.php?token=XXXXXXXX
 *
 * Le token est défini ci-dessous. Pour la sécurité, supprimez ce fichier
 * dès que les caches sont vidés.
 */

// === CONFIGURATION ============================================================
// Modifiez ce token avant de déployer en production.
$EXPECTED_TOKEN = 'chamse-emergency-2026';
// =============================================================================

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_GET['token']) || !hash_equals($EXPECTED_TOKEN, (string) $_GET['token'])) {
    http_response_code(403);
    echo "Forbidden. Token manquant ou invalide.\n";
    echo "Usage : ?token=VOTRE_TOKEN\n";
    exit;
}

$root    = realpath(__DIR__ . '/..');
$cleared = [];
$errors  = [];

// 1. Fichiers de cache Laravel à supprimer
$cacheFiles = [
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/config.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/events.php',
];

foreach ($cacheFiles as $file) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (file_exists($path)) {
        if (@unlink($path)) {
            $cleared[] = "OK   supprimé : {$file}";
        } else {
            $errors[] = "FAIL impossible de supprimer : {$file}";
        }
    } else {
        $cleared[] = "SKIP absent : {$file}";
    }
}

// 2. Cache des vues compilées
$viewsDir = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views';
if (is_dir($viewsDir)) {
    $count = 0;
    foreach (glob($viewsDir . DIRECTORY_SEPARATOR . '*.php') as $f) {
        if (@unlink($f)) {
            $count++;
        }
    }
    $cleared[] = "OK   {$count} vue(s) compilée(s) supprimée(s) dans storage/framework/views/";
}

// 3. Cache d'application (fichiers)
$appCacheDir = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'data';
if (is_dir($appCacheDir)) {
    $count = 0;
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appCacheDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($rii as $entry) {
        if ($entry->isFile() && $entry->getFilename() !== '.gitignore') {
            if (@unlink($entry->getPathname())) {
                $count++;
            }
        }
    }
    $cleared[] = "OK   {$count} fichier(s) de cache applicatif supprimé(s)";
}

// 4. OPcache : crucial sur les hébergements PHP-FPM
//    Même après avoir supprimé routes-v7.php, OPcache garde l'ancienne version
//    en mémoire jusqu'au redémarrage de PHP-FPM. On force la réinit ici.
if (function_exists('opcache_reset')) {
    if (@opcache_reset()) {
        $cleared[] = "OK   OPcache réinitialisé (mémoire PHP)";
    } else {
        $cleared[] = "WARN opcache_reset() a échoué (peut-être désactivé via opcache.restrict_api)";
    }
} else {
    $cleared[] = "SKIP OPcache n'est pas chargé sur ce serveur";
}

// 5. Forcer la réinit ciblée des fichiers Laravel encore en mémoire OPcache
if (function_exists('opcache_invalidate')) {
    $toInvalidate = [
        $root . '/routes/web.php',
        $root . '/routes/api.php',
        $root . '/routes/console.php',
        $root . '/bootstrap/app.php',
        $root . '/app/Http/Controllers/Admin/ProductController.php',
    ];
    foreach ($toInvalidate as $f) {
        if (file_exists($f)) {
            @opcache_invalidate($f, true);
        }
    }
    $cleared[] = "OK   Fichiers source invalidés dans OPcache";
}

echo "==== Cache clear effectué à " . date('Y-m-d H:i:s') . " ====\n\n";
foreach ($cleared as $line) {
    echo $line . "\n";
}
if (!empty($errors)) {
    echo "\n!!! ERREURS !!!\n";
    foreach ($errors as $err) {
        echo $err . "\n";
    }
}

echo "\n==== Vérification : routes admin produit/variant ====\n";
$routesCache = $root . '/bootstrap/cache/routes-v7.php';
if (file_exists($routesCache)) {
    echo "ATTENTION : bootstrap/cache/routes-v7.php existe TOUJOURS — la suppression a échoué.\n";
} else {
    echo "OK : bootstrap/cache/routes-v7.php n'existe plus, Laravel rechargera routes/web.php au prochain hit.\n";
}

echo "\nTerminé. SUPPRIMEZ CE FICHIER (public/__chamse-flush.php) une fois que tout marche.\n";
