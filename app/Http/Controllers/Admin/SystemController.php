<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SystemController extends Controller
{
    /**
     * Page d'administration système : état du déploiement,
     * version Git, OPcache, espace disque et bouton de déploiement.
     */
    public function index()
    {
        return view('admin.system.index', [
            'systemInfo' => $this->collectSystemInfo(),
        ]);
    }

    /**
     * Lance la commande deploy:after-pull et retourne sa sortie.
     * Réservé aux admins.
     */
    public function deploy(Request $request)
    {
        $request->validate([
            'skip_migrate' => 'sometimes|boolean',
            'skip_cache'   => 'sometimes|boolean',
        ]);

        $options = [];
        if ($request->boolean('skip_migrate')) {
            $options['--skip-migrate'] = true;
        }
        if ($request->boolean('skip_cache')) {
            $options['--skip-cache'] = true;
        }

        try {
            $exit = Artisan::call('deploy:after-pull', $options);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            $exit = 1;
            $output = "ERREUR FATALE :\n" . $e->getMessage() . "\n" . $e->getTraceAsString();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'   => $exit === 0,
                'exit_code' => $exit,
                'output'    => $output,
                'system'    => $this->collectSystemInfo(),
            ]);
        }

        return back()
            ->with($exit === 0 ? 'success' : 'error', $exit === 0 ? 'Déploiement terminé.' : 'Le déploiement a échoué (voir détails).')
            ->with('deploy_output', $output);
    }

    /**
     * Récupère un état complet du système pour la page d'admin.
     */
    private function collectSystemInfo(): array
    {
        $headFile = base_path('.git/HEAD');
        $gitHead = null;
        $gitBranch = null;
        $gitCommitMessage = null;
        $gitCommitDate = null;

        if (File::exists($headFile)) {
            $head = trim(File::get($headFile));
            if (str_starts_with($head, 'ref: ')) {
                $ref = substr($head, 5);
                $gitBranch = basename($ref);
                $refFile = base_path('.git/' . $ref);
                if (File::exists($refFile)) {
                    $gitHead = substr(trim(File::get($refFile)), 0, 7);
                }
            } else {
                $gitHead = substr($head, 0, 7);
                $gitBranch = '(detached)';
            }

            // Lire le dernier commit depuis logs/HEAD si dispo
            $logFile = base_path('.git/logs/HEAD');
            if (File::exists($logFile)) {
                $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $lastLine = end($lines);
                if ($lastLine && preg_match('/(\d{10}) [+-]\d{4}\t(.+)$/', $lastLine, $m)) {
                    $gitCommitDate = date('d/m/Y H:i', (int) $m[1]);
                    $gitCommitMessage = $m[2];
                }
            }
        }

        $opcacheStatus = function_exists('opcache_get_status') ? @opcache_get_status(false) : null;
        $storageLink = public_path('storage');
        $storagePath = storage_path('app/public');

        $cacheFiles = [
            'routes-v7' => 'bootstrap/cache/routes-v7.php',
            'config'    => 'bootstrap/cache/config.php',
            'services'  => 'bootstrap/cache/services.php',
            'packages'  => 'bootstrap/cache/packages.php',
            'events'    => 'bootstrap/cache/events.php',
        ];
        $caches = [];
        foreach ($cacheFiles as $key => $rel) {
            $abs = base_path($rel);
            $caches[$key] = [
                'present' => File::exists($abs),
                'mtime'   => File::exists($abs) ? date('d/m/Y H:i', File::lastModified($abs)) : null,
            ];
        }

        return [
            'app' => [
                'env'      => config('app.env'),
                'debug'    => config('app.debug'),
                'url'      => config('app.url'),
                'locale'   => config('app.locale'),
                'timezone' => config('app.timezone'),
            ],
            'php' => [
                'version'            => PHP_VERSION,
                'memory_limit'       => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize'=> ini_get('upload_max_filesize'),
                'post_max_size'      => ini_get('post_max_size'),
                'extensions'         => [
                    'gd'       => extension_loaded('gd'),
                    'fileinfo' => extension_loaded('fileinfo'),
                    'pdo'      => extension_loaded('pdo'),
                    'mbstring' => extension_loaded('mbstring'),
                    'opcache'  => extension_loaded('Zend OPcache') || extension_loaded('opcache'),
                ],
            ],
            'opcache' => [
                'enabled' => $opcacheStatus['opcache_enabled'] ?? false,
                'cached_scripts' => $opcacheStatus['opcache_statistics']['num_cached_scripts'] ?? null,
                'memory_used'    => isset($opcacheStatus['memory_usage']['used_memory'])
                    ? round($opcacheStatus['memory_usage']['used_memory'] / 1024 / 1024, 1) . ' Mo'
                    : null,
                'memory_free'    => isset($opcacheStatus['memory_usage']['free_memory'])
                    ? round($opcacheStatus['memory_usage']['free_memory'] / 1024 / 1024, 1) . ' Mo'
                    : null,
            ],
            'git' => [
                'branch'         => $gitBranch,
                'head'           => $gitHead,
                'commit_message' => $gitCommitMessage,
                'commit_date'    => $gitCommitDate,
            ],
            'storage' => [
                'public_link_exists' => File::exists($storageLink),
                'storage_path_exists'=> File::exists($storagePath),
                'storage_writable'   => is_writable($storagePath),
                'logs_writable'      => is_writable(storage_path('logs')),
            ],
            'caches' => $caches,
            'now'    => now()->format('d/m/Y H:i:s'),
        ];
    }
}
