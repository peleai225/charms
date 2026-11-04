<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Sert les fichiers du disque public (photos produits, bannières…).
 *
 * Sur LiteSpeed, /storage/* passe par PHP (le lien symbolique n'est pas suivi).
 * La route intégrée de Laravel interdit alors tout cache (no-store) : chaque page
 * retéléchargeait toutes ses photos. Ici, le téléphone garde les images 7 jours
 * et ne les redemande ensuite que si elles ont changé (304).
 */
class StorageFileController extends Controller
{
    private const MAX_AGE = 604800;

    public function __invoke(Request $request, string $path): BinaryFileResponse
    {
        $disk = Storage::disk('public');

        abort_if(str_contains($path, '..') || ! $disk->exists($path), 404);

        $response = response()->file($disk->path($path), [
            'Cache-Control' => 'public, max-age='.self::MAX_AGE,
            // Permet à LiteSpeed de servir les visites suivantes sans démarrer PHP
            'X-LiteSpeed-Cache-Control' => 'public,max-age='.self::MAX_AGE,
        ]);
        $response->setAutoEtag();
        $response->isNotModified($request);

        return $response;
    }
}
