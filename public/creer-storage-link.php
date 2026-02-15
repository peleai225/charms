<?php
/**
 * Créer le lien symbolique storage sans terminal (hébergement sans SSH).
 * À exécuter une seule fois via le navigateur, puis SUPPRIMER ce fichier.
 *
 * URL : https://votre-site.com/creer-storage-link.php?token=VOTRE_CLE
 * Remplacez VOTRE_CLE par la valeur de STORAGE_LINK_TOKEN ci-dessous.
 */

// Sécurité : changez cette clé (lettres/chiffres) pour que personne d'autre ne puisse lancer le script
$secretToken = 'Chamse2025StorageLink';

$token = $_GET['token'] ?? '';

if ($token !== $secretToken) {
    header('Content-Type: text/html; charset=utf-8');
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Accès refusé</title></head><body>';
    echo '<h1>Accès refusé</h1><p>Utilisez l’URL avec le paramètre <code>?token=...</code> correct.</p>';
    echo '</body></html>';
    exit;
}

$link = __DIR__ . '/storage';           // public/storage
$target = __DIR__ . '/../storage/app/public';

// Créer storage/app/public si absent (fréquent sur hébergement sans déploiement complet)
if (!is_dir($target)) {
    $storageApp = __DIR__ . '/../storage/app';
    if (!is_dir($storageApp)) {
        @mkdir($storageApp, 0755, true);
    }
    if (!is_dir($target)) {
        @mkdir($target, 0755, true);
    }
}

if (!is_dir($target)) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Erreur</title></head><body>';
    echo '<h1>Erreur</h1><p>Impossible de créer <code>storage/app/public</code>. Vérifiez les droits d’écriture sur le dossier <code>storage/</code>.</p>';
    echo '</body></html>';
    exit;
}

$success = false;
$message = '';

if (file_exists($link)) {
    if (is_link($link) || (PHP_OS_FAMILY === 'Windows' && is_dir($link))) {
        $message = 'Le lien storage existe déjà. Rien à faire.';
        $success = true;
    } else {
        $message = 'Un fichier ou dossier <code>public/storage</code> existe déjà et n’est pas un lien. Supprimez-le ou renommez-le puis réessayez.';
    }
} else {
    if (PHP_OS_FAMILY === 'Windows') {
        // Windows : junction (recommandé sans droits admin) ou symlink
        if (function_exists('symlink')) {
            $success = @symlink($target, $link);
            $message = $success ? 'Lien symbolique créé avec succès.' : 'Échec symlink. Vérifiez les droits.';
        } else {
            $success = @exec('mklink /J "' . str_replace('/', '\\', $link) . '" "' . str_replace('/', '\\', $target) . '"');
            $message = $success ? 'Junction créée avec succès.' : 'Échec. Lancez ce script en ligne de commande avec les droits nécessaires.';
        }
    } else {
        $success = @symlink($target, $link);
        $message = $success ? 'Lien symbolique créé avec succès.' : 'Échec. Vérifiez les droits du serveur (PHP doit pouvoir créer un lien).';
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow">
    <title>Storage link</title>
    <style>
        body { font-family: sans-serif; max-width: 560px; margin: 2rem auto; padding: 0 1rem; }
        h1 { font-size: 1.25rem; }
        .ok { color: #059669; }
        .err { color: #dc2626; }
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; }
        .warn { background: #fef3c7; border: 1px solid #f59e0b; padding: 1rem; border-radius: 8px; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <h1><?= $success ? 'Succès' : 'Échec' ?></h1>
    <p class="<?= $success ? 'ok' : 'err' ?>"><?= $message ?></p>
    <div class="warn">
        <strong>Important :</strong> supprimez ce fichier après utilisation pour des raisons de sécurité :<br>
        <code>public/creer-storage-link.php</code>
    </div>
</body>
</html>
