<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class ViteHelper
{
    /**
     * Charge les assets Vite depuis le manifest.json en production
     * Retourne une chaîne vide en développement (on utilisera @vite() directement)
     */
    public function renderAssets(array $assets): string
    {
        try {
            // En développement local, on n'utilise jamais ViteHelper
            // (on utilise @vite() directement dans les vues)
            if (app()->environment('local')) {
                return '';
            }

            // En production, charger depuis le manifest.json
            $manifestPath = public_path('build/manifest.json');
            
            if (!File::exists($manifestPath)) {
                // Si le manifest n'existe pas, essayer de charger depuis build/assets directement
                return $this->loadAssetsDirectly($assets);
            }

            $manifestContent = File::get($manifestPath);
            $manifest = json_decode($manifestContent, true);
            
            if (!$manifest || json_last_error() !== JSON_ERROR_NONE) {
                return $this->loadAssetsDirectly($assets);
            }

            $html = '';
            
            foreach ($assets as $asset) {
                // Chercher l'asset dans le manifest
                if (isset($manifest[$asset])) {
                    $entry = $manifest[$asset];
                    
                    // Charger le CSS
                    if (isset($entry['css']) && is_array($entry['css'])) {
                        foreach ($entry['css'] as $css) {
                            $html .= '<link rel="stylesheet" href="' . e(asset('build/' . $css)) . '">' . "\n    ";
                        }
                    }
                    
                    // Charger le JS
                    if (isset($entry['file'])) {
                        $html .= '<script type="module" src="' . e(asset('build/' . $entry['file'])) . '"></script>' . "\n    ";
                    }
                } else {
                    // Si l'asset n'est pas dans le manifest, essayer de le charger directement
                    $html .= $this->loadAssetDirectly($asset);
                }
            }
            
            return $html;
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une chaîne vide pour ne pas casser la page
            return '';
        }
    }

    /**
     * Charge les assets directement depuis build/assets
     */
    private function loadAssetsDirectly(array $assets): string
    {
        try {
            $html = '';
            
            // Chercher les fichiers CSS et JS compilés
            $buildPath = public_path('build/assets');
            
            if (!File::isDirectory($buildPath)) {
                return '';
            }
            
            $files = File::files($buildPath);
            
            if (!is_array($files)) {
                return '';
            }
            
            foreach ($files as $file) {
                if (!is_object($file) || !method_exists($file, 'getFilename')) {
                    continue;
                }
                
                $filename = $file->getFilename();
                $relativePath = 'build/assets/' . $filename;
                
                if (str_ends_with($filename, '.css')) {
                    $html .= '<link rel="stylesheet" href="' . e(asset($relativePath)) . '">' . "\n    ";
                } elseif (str_ends_with($filename, '.js')) {
                    $html .= '<script type="module" src="' . e(asset($relativePath)) . '"></script>' . "\n    ";
                }
            }
            
            return $html;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Charge un asset spécifique directement
     */
    private function loadAssetDirectly(string $asset): string
    {
        try {
            // Convertir resources/css/app.css en build/assets/app-xxx.css
            $buildPath = public_path('build/assets');
            
            if (!File::isDirectory($buildPath)) {
                return '';
            }
            
            $files = File::files($buildPath);
            
            if (!is_array($files)) {
                return '';
            }
            
            $basename = pathinfo($asset, PATHINFO_FILENAME);
            
            foreach ($files as $file) {
                if (!is_object($file) || !method_exists($file, 'getFilename')) {
                    continue;
                }
                
                $filename = $file->getFilename();
                
                if (str_starts_with($filename, $basename)) {
                    $relativePath = 'build/assets/' . $filename;
                    
                    if (str_ends_with($filename, '.css')) {
                        return '<link rel="stylesheet" href="' . e(asset($relativePath)) . '">' . "\n    ";
                    } elseif (str_ends_with($filename, '.js')) {
                        return '<script type="module" src="' . e(asset($relativePath)) . '"></script>' . "\n    ";
                    }
                }
            }
            
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
}

