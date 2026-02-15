<?php
/**
 * Script de configuration Laravel (cache, storage) — sans symlink requis
 * À placer dans public_html/ sur le serveur.
 * SUPPRIMEZ après utilisation.
 */

// Racine Laravel : depuis public_html → repositories/charms
$laravelBase = file_exists(__DIR__.'/../repositories/charms/vendor/autoload.php')
    ? realpath(__DIR__.'/../repositories/charms')
    : __DIR__.'/..';

if (!file_exists($laravelBase.'/vendor/autoload.php')) {
    http_response_code(500);
    die('Erreur : vendor/ introuvable. Chemin testé : '.$laravelBase);
}

require $laravelBase.'/vendor/autoload.php';
$app = require_once $laravelBase.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/html; charset=utf-8');

$steps = [];

// 1. Vider les caches
try {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    $steps[] = ['ok', 'Vidage des caches'];
} catch (\Throwable $e) {
    $steps[] = ['err', 'Cache : '.$e->getMessage()];
}

// 2. Lien storage — ignoré si symlink() désactivé (images servies par la route Laravel)
$steps[] = ['info', 'Storage : symlink() désactivé sur ce serveur — les images sont servies par la route Laravel (route storage.serve dans web.php). Aucune action requise.'];

// 3. Config / route / view cache (optionnel en prod)
try {
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    $steps[] = ['ok', 'Caches de prod créés'];
} catch (\Throwable $e) {
    $steps[] = ['warn', 'Cache prod : '.$e->getMessage()];
}

?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Setup Laravel</title>
<style>body{font-family:sans-serif;max-width:500px;margin:40px auto;padding:20px}
.step{padding:8px 12px;margin:6px 0;border-radius:6px}
.ok{background:#d1fae5;color:#065f46}.err{background:#fee2e2;color:#991b1b}
.warn{background:#fef3c7;color:#92400e}.info{background:#e0e7ff;color:#3730a3}
.alert{background:#fef3c7;border-left:4px solid #f59e0b;padding:12px;margin-top:20px}</style>
</head><body>
<h1>Configuration Laravel</h1>
<?php foreach ($steps as $s): ?>
<div class="step <?php echo $s[0]; ?>"><?php echo htmlspecialchars($s[1]); ?></div>
<?php endforeach; ?>
<div class="alert"><strong>Supprimez setup.php</strong> après utilisation.</div>
<p><a href="/">→ Accéder au site</a></p>
</body></html>
