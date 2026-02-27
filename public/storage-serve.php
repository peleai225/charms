<?php
/**
 * Serveur d'images standalone — pour hébergement sans symlink
 *
 * À placer dans public_html/ et renommer en "storage.php"
 * Puis modifier .htaccess : remplacer la règle storage par :
 *   RewriteRule ^storage/(.*)$ storage-serve.php?f=$1 [L]
 *
 * Ou créer public_html/storage/.htaccess avec :
 *   RewriteEngine On
 *   RewriteRule ^(.*)$ ../storage.php?f=$1 [L,QSA]
 * Et un fichier public_html/storage/index.php qui fait :
 *   <?php include __DIR__.'/../storage.php'; (non, ça ne marche pas)
 *
 * Solution simple : ce fichier s'appelle storage.php, placé dans public_html.
 * L'URL /storage/products/1/xxx.jpg → storage.php?f=products/1/xxx.jpg
 */
$file = $_GET['f'] ?? '';
if ($file === '' || strpos($file, '..') !== false) {
    http_response_code(404);
    exit;
}

$chamsePath = __DIR__ . '/chamse';
if (!is_dir($chamsePath)) {
    $chamsePath = dirname(__DIR__);
}

$storagePath = $chamsePath . '/storage/app/public/' . $file;
$allowedRoot = realpath($chamsePath . '/storage/app/public');

if (!$allowedRoot || !file_exists($storagePath) || !is_file($storagePath)) {
    http_response_code(404);
    exit;
}

$realPath = realpath($storagePath);
if ($realPath === false || $allowedRoot === false || strpos($realPath, $allowedRoot) !== 0) {
    http_response_code(404);
    exit;
}

$mime = mime_content_type($realPath) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($realPath));
readfile($realPath);
