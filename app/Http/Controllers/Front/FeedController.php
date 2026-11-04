<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Flux produits pour le catalogue Meta (Advantage+ catalogue).
 * L'id de chaque ligne = l'id produit envoyé par le pixel dans content_ids.
 * Aucune donnée personnelle.
 */
class FeedController extends Controller
{
    private const COLUMNS = [
        'id', 'title', 'description', 'availability', 'condition', 'price', 'sale_price',
        'link', 'image_link', 'additional_image_link', 'brand', 'product_type',
    ];

    public function metaCsv()
    {
        $csv = Cache::remember('feed.meta.csv', 3600, fn () => $this->buildCsv());

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Version JPEG d'une photo WebP sans version og/ (Meta ne lit pas toujours le WebP).
     * Convertie une seule fois puis servie depuis le disque.
     */
    public function imageJpeg(ProductImage $image)
    {
        $jpgPath = 'feed-jpg/'.$image->id.'.jpg';
        $disk = Storage::disk('public');

        if (! $disk->exists($jpgPath)) {
            $src = $disk->exists($image->path) ? @imagecreatefromstring($disk->get($image->path)) : false;
            if (! $src) {
                abort(404);
            }
            $w = imagesx($src);
            $h = imagesy($src);
            $dst = imagecreatetruecolor($w, $h);
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255)); // fond blanc (transparence)
            imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
            $disk->makeDirectory('feed-jpg');
            imagejpeg($dst, $disk->path($jpgPath), 90);
            imagedestroy($src);
            imagedestroy($dst);
        }

        return response()->file($disk->path($jpgPath), [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    protected function buildCsv(): string
    {
        $brand = Setting::get('site_name') ?: config('app.name');

        $out = fopen('php://temp', 'r+');
        fwrite($out, implode(',', self::COLUMNS)."\n");

        Product::active()
            ->with([
                'images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('position'),
                'variants' => fn ($q) => $q->where('is_active', true),
                'category.parent.parent',
            ])
            ->orderBy('id')
            ->chunk(200, function ($products) use ($out, $brand) {
                foreach ($products as $product) {
                    $this->writeRow($out, $this->row($product, $brand));
                }
            });

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $csv;
    }

    protected function row(Product $product, string $brand): array
    {
        $title = $this->cleanTitle($product->name);

        $description = $this->cleanText($product->description) ?: $this->cleanText($product->short_description);
        if ($description === '') {
            $description = "{$title} – disponible sur {$brand}, livraison partout en Côte d'Ivoire, paiement à la livraison.";
        }

        $stock = $product->has_variants && $product->variants->isNotEmpty()
            ? $product->variants->sum('stock_quantity')
            : $product->stock_quantity;
        $inStock = ! $product->track_stock || $stock > 0;

        // price = prix normal (barré s'il existe), sale_price = prix réellement payé s'il est inférieur
        $selling = (float) $product->sale_price;
        $compare = (float) $product->compare_price;
        $onSale = $compare > $selling && $selling > 0;

        $images = $product->images->map(fn ($img) => $this->imageUrl($img))->filter()->values();

        return [
            (string) $product->id,
            $title,
            mb_substr($description, 0, 9999),
            $inStock ? 'in stock' : 'out of stock',
            'new',
            $this->money($onSale ? $compare : $selling),
            $onSale ? $this->money($selling) : '',
            $this->absolute('/produit/'.$product->slug),
            $images->first() ?? '',
            $images->slice(1, 10)->implode(','),
            $brand,
            $product->category ? $product->category->full_path : '',
        ];
    }

    /**
     * Meilleure image disponible en JPEG/PNG :
     * og/uuid.jpg (jusqu'à 1200 px, non recadrée) > fichier JPEG/PNG > conversion JPEG.
     */
    protected function imageUrl(ProductImage $img): ?string
    {
        if (! $img->path) {
            return null;
        }

        $og = preg_replace('#/medium/(.+)\.webp$#', '/og/$1.jpg', $img->path);
        if ($og !== $img->path && Storage::disk('public')->exists($og)) {
            return $this->absolute('/storage/'.$og);
        }

        if (preg_match('/\.(jpe?g|png)$/i', $img->path)) {
            return $this->absolute('/storage/'.$img->path);
        }

        return $this->absolute('/feed/images/'.$img->id.'.jpg');
    }

    protected function cleanTitle(?string $name): string
    {
        $title = trim(preg_replace('/\s+/u', ' ', $this->stripEmojis((string) $name)));

        // Titre tout en majuscules → première lettre en majuscule seulement
        if (preg_match('/\p{L}{3,}/u', $title) && mb_strtoupper($title) === $title) {
            $lower = mb_strtolower($title);
            $title = mb_strtoupper(mb_substr($lower, 0, 1)).mb_substr($lower, 1);
        }

        return mb_substr($title, 0, 150);
    }

    protected function cleanText(?string $html): string
    {
        $text = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ' ', (string) $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $this->stripEmojis($text)));
    }

    protected function stripEmojis(string $text): string
    {
        return trim(preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}\x{200D}\x{2B00}-\x{2BFF}]/u', '', $text));
    }

    protected function money(float $amount): string
    {
        return ((int) round($amount)).' XOF';
    }

    protected function absolute(string $path): string
    {
        // URL du site qui sert le flux, toujours en https
        return preg_replace('#^http://#', 'https://', url($path));
    }

    protected function writeRow($out, array $fields): void
    {
        fputcsv($out, $fields, ',', '"', '');
    }
}
