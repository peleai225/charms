<?php
/**
 * Script de vérification — à placer dans public_html/ et exécuter une fois
 * URL : https://votre-domaine.com/verifier-env.php
 * SUPPRIMER CE FICHIER après utilisation (sécurité)
 */
header('Content-Type: text/plain; charset=utf-8');

$chamsePath = __DIR__ . '/chamse';
if (!is_dir($chamsePath)) {
    $chamsePath = dirname(__DIR__);
}

$envPath = $chamsePath . '/.env';
$configCachePath = $chamsePath . '/bootstrap/cache/config.php';

echo "=== Diagnostic Chamse ===\n\n";
echo "Dossier Laravel : " . $chamsePath . "\n";
echo "Existe : " . (is_dir($chamsePath) ? 'OUI' : 'NON') . "\n\n";

echo "Fichier .env : " . $envPath . "\n";
echo "Existe : " . (file_exists($envPath) ? 'OUI' : 'NON') . "\n\n";

if (file_exists($envPath)) {
    $env = file_get_contents($envPath);
    echo "DB_USERNAME défini : " . (preg_match('/DB_USERNAME=.+/', $env) ? 'OUI' : 'NON') . "\n";
    echo "DB_PASSWORD défini : " . (preg_match('/DB_PASSWORD=.+/', $env) && !preg_match('/DB_PASSWORD=\s*$/m', $env) ? 'OUI' : 'NON') . "\n";
    echo "APP_KEY défini : " . (preg_match('/APP_KEY=base64:.+/', $env) ? 'OUI' : 'NON') . "\n";
    if (!preg_match('/APP_KEY=base64:.+/', $env)) {
        $newKey = 'base64:' . base64_encode(random_bytes(32));
        echo "\n>>> Ajoutez cette ligne dans chamse/.env :\n";
        echo "APP_KEY=" . $newKey . "\n";
    }
}

echo "\nCache config (bootstrap/cache/config.php) :\n";
echo "Existe : " . (file_exists($configCachePath) ? 'OUI - SUPPRIMEZ-LE pour forcer la lecture du .env' : 'NON') . "\n";

if (file_exists($configCachePath)) {
    if (@unlink($configCachePath)) {
        echo ">>> Cache config SUPPRIMÉ avec succès.\n";
    } else {
        echo ">>> Suppression manuelle requise : supprimez bootstrap/cache/config.php via FTP.\n";
    }
}

echo "\n=== Fin ===\n";
