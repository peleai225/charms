<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Setting;
use App\Services\AiService;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Recherche par photo côté client.
 * La photo est analysée puis oubliée : elle n'est jamais enregistrée sur le serveur.
 */
class ImageSearchController extends Controller
{
    public function search(Request $request, AiService $ai)
    {
        if (! $ai->isConfigured() || Setting::get('ai_image_search_enabled', '1') !== '1') {
            return response()->json(['message' => 'La recherche par photo est indisponible pour le moment.'], 503);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'image.max' => 'La photo est trop lourde (5 Mo maximum).',
            'image.image' => 'Le fichier doit être une photo.',
        ]);

        $file = $request->file('image');

        try {
            $result = $ai->describeForSearch(file_get_contents($file->getRealPath()), $file->getMimeType());
        } catch (RuntimeException|\Illuminate\Http\Client\ConnectionException $e) {
            report($e);

            return response()->json(['message' => "Nous n'avons pas pu analyser la photo. Réessayez ou utilisez la recherche par mots."], 422);
        }

        if (empty($result['keywords']) && ! $result['category_id']) {
            return response()->json(['message' => "Nous n'avons pas reconnu d'article sur cette photo. Essayez avec une photo plus nette, centrée sur l'article."], 422);
        }

        return response()->json([
            'label' => $result['label'],
            'terms' => implode(',', $result['keywords']),
            'category' => $result['category_id'] ? Category::find($result['category_id'])?->slug : null,
        ]);
    }
}
