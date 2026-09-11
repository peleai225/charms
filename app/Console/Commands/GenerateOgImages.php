<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateOgImages extends Command
{
    protected $signature   = 'images:generate-og {--force : Régénérer même si og/ existe déjà}';
    protected $description = 'Génère les versions JPEG og/ pour les réseaux sociaux (Facebook/WhatsApp ne supportent pas WebP)';

    public function handle(): int
    {
        $images = ProductImage::all();
        $bar    = $this->output->createProgressBar($images->count());
        $bar->start();

        $generated = 0;
        $skipped   = 0;
        $errors    = 0;

        foreach ($images as $img) {
            // Seulement les images medium WebP
            if (!preg_match('#^(.+)/medium/(.+)\.webp$#', $img->path, $m)) {
                $bar->advance();
                $skipped++;
                continue;
            }

            $directory  = $m[1];
            $uuid       = $m[2];
            $ogPath     = $directory . '/og/' . $uuid . '.jpg';

            if (!$this->option('force') && Storage::disk('public')->exists($ogPath)) {
                $bar->advance();
                $skipped++;
                continue;
            }

            $srcPath = storage_path('app/public/' . $img->path);
            if (!file_exists($srcPath)) {
                $bar->advance();
                $errors++;
                continue;
            }

            try {
                $src = imagecreatefromwebp($srcPath);
                if (!$src) { $errors++; $bar->advance(); continue; }

                $origW = imagesx($src);
                $origH = imagesy($src);
                $ogW   = min($origW, 1200);
                $ogH   = (int) round($ogW * $origH / $origW);

                $dst = imagecreatetruecolor($ogW, $ogH);
                imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $ogW, $ogH, $origW, $origH);
                imagedestroy($src);

                Storage::disk('public')->makeDirectory($directory . '/og');
                $fullOgPath = storage_path('app/public/' . $ogPath);
                imagejpeg($dst, $fullOgPath, 90);
                imagedestroy($dst);

                $generated++;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("Erreur pour {$img->path}: {$e->getMessage()}");
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Terminé — {$generated} générées, {$skipped} ignorées, {$errors} erreurs.");

        return self::SUCCESS;
    }
}
