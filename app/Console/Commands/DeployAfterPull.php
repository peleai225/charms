<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DeployAfterPull extends Command
{
    protected $signature = 'deploy:after-pull
                            {--skip-migrate : Ne pas exécuter les migrations}
                            {--skip-cache : Ne pas regénérer les caches Laravel}
                            {--skip-storage-link : Ne pas créer le lien storage}';

    protected $description = 'Tâches post-pull : caches, migrations, OPcache, lien storage. À lancer après chaque git pull en production.';

    public function handle(): int
    {
        $start = microtime(true);
        $this->info('==== Déploiement post-pull démarré ====');
        $this->line('');

        $steps = [];

        // 1. Nettoyer tous les caches Laravel
        $this->info('[1/6] Nettoyage des caches Laravel...');
        try {
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            $this->line('      OK : route, view, config et cache nettoyés');
            $steps[] = ['Caches Laravel', 'OK'];
        } catch (\Throwable $e) {
            $this->error('      FAIL : ' . $e->getMessage());
            $steps[] = ['Caches Laravel', 'FAIL : ' . $e->getMessage()];
        }
        $this->line('');

        // 2. Suppression manuelle des fichiers cache résiduels
        $this->info('[2/6] Suppression des fichiers cache résiduels...');
        $cacheFiles = [
            'bootstrap/cache/routes-v7.php',
            'bootstrap/cache/config.php',
            'bootstrap/cache/services.php',
            'bootstrap/cache/packages.php',
            'bootstrap/cache/events.php',
        ];
        $removed = 0;
        foreach ($cacheFiles as $rel) {
            $path = base_path($rel);
            if (File::exists($path)) {
                if (@unlink($path)) {
                    $removed++;
                }
            }
        }
        $this->line("      OK : {$removed} fichier(s) supprimé(s)");
        $steps[] = ['Fichiers cache', "OK ({$removed} supprimés)"];
        $this->line('');

        // 3. Migrations
        if ($this->option('skip-migrate')) {
            $this->warn('[3/6] Migrations sautées (--skip-migrate)');
            $steps[] = ['Migrations', 'SKIPPED'];
        } else {
            $this->info('[3/6] Exécution des migrations...');
            try {
                Artisan::call('migrate', ['--force' => true]);
                $output = trim(Artisan::output());
                $this->line('      ' . str_replace("\n", "\n      ", $output ?: 'Aucune migration en attente'));
                $steps[] = ['Migrations', 'OK'];
            } catch (\Throwable $e) {
                $this->error('      FAIL : ' . $e->getMessage());
                $steps[] = ['Migrations', 'FAIL : ' . $e->getMessage()];
            }
        }
        $this->line('');

        // 4. Lien storage
        if ($this->option('skip-storage-link')) {
            $this->warn('[4/6] Lien storage sauté (--skip-storage-link)');
            $steps[] = ['Lien storage', 'SKIPPED'];
        } else {
            $this->info('[4/6] Vérification du lien storage...');
            $publicStorage = public_path('storage');
            if (File::exists($publicStorage)) {
                $this->line('      OK : public/storage existe déjà');
                $steps[] = ['Lien storage', 'OK (déjà présent)'];
            } else {
                try {
                    Artisan::call('storage:link');
                    $this->line('      OK : public/storage créé');
                    $steps[] = ['Lien storage', 'OK (créé)'];
                } catch (\Throwable $e) {
                    $this->error('      FAIL : ' . $e->getMessage());
                    $steps[] = ['Lien storage', 'FAIL : ' . $e->getMessage()];
                }
            }
        }
        $this->line('');

        // 5. Vue cache et config cache pour la prod (perfs)
        if ($this->option('skip-cache')) {
            $this->warn('[5/6] Re-cache prod sauté (--skip-cache)');
            $steps[] = ['Re-cache prod', 'SKIPPED'];
        } else {
            $this->info('[5/6] Génération des caches de production...');
            $cacheRebuild = [];
            try {
                if (!app()->environment('local')) {
                    Artisan::call('config:cache');
                    Artisan::call('route:cache');
                    Artisan::call('view:cache');
                    Artisan::call('event:cache');
                    $cacheRebuild = ['config', 'route', 'view', 'event'];
                    $this->line('      OK : ' . implode(', ', $cacheRebuild) . ' (re)cachés');
                    $steps[] = ['Re-cache prod', 'OK (' . implode(', ', $cacheRebuild) . ')'];
                } else {
                    $this->line('      SKIP : APP_ENV=local, on ne cache pas en dev');
                    $steps[] = ['Re-cache prod', 'SKIPPED (env=local)'];
                }
            } catch (\Throwable $e) {
                $this->error('      FAIL : ' . $e->getMessage());
                $steps[] = ['Re-cache prod', 'FAIL : ' . $e->getMessage()];
            }
        }
        $this->line('');

        // 6. OPcache reset
        $this->info('[6/6] Réinitialisation OPcache...');
        if (function_exists('opcache_reset')) {
            if (@opcache_reset()) {
                $this->line('      OK : OPcache vidé');
                $steps[] = ['OPcache', 'OK'];
            } else {
                $this->warn('      WARN : opcache_reset() a échoué (opcache.restrict_api ?)');
                $steps[] = ['OPcache', 'WARN (restrict_api ?)'];
            }
        } else {
            $this->warn('      WARN : OPcache n\'est pas chargé. Performance dégradée en prod !');
            $this->warn('              Demandez à votre hébergeur d\'activer l\'extension opcache.');
            $steps[] = ['OPcache', 'NOT LOADED (perfs dégradées)'];
        }
        $this->line('');

        // Récap
        $duration = round(microtime(true) - $start, 2);
        $this->info('==== Récapitulatif ====');
        foreach ($steps as [$label, $status]) {
            $color = str_starts_with($status, 'FAIL') ? 'error'
                   : (str_starts_with($status, 'WARN') || str_starts_with($status, 'NOT') || str_starts_with($status, 'SKIPPED') ? 'warn' : 'line');
            $this->{$color === 'error' ? 'error' : ($color === 'warn' ? 'warn' : 'line')}(sprintf('  %-22s %s', $label, $status));
        }
        $this->line('');
        $this->info("==== Terminé en {$duration}s ====");

        $hasFail = collect($steps)->contains(fn($s) => str_starts_with($s[1], 'FAIL'));
        return $hasFail ? self::FAILURE : self::SUCCESS;
    }
}
