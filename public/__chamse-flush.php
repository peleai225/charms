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
echo "\nTerminé. SUPPRIMEZ CE FICHIER (public/__clear-cache.php) une fois utilisé.\n";
