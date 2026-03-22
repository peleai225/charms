<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class WhatsAppController extends Controller
{
    /**
     * Page de gestion du canal WhatsApp
     */
    public function index()
    {
        $productsCount = Product::active()->count();
        $waNumber      = Setting::get('social_whatsapp', '');

        return view('admin.whatsapp.index', compact('productsCount', 'waNumber'));
    }

    /**
     * Export catalogue au format CSV compatible Meta Commerce Manager / WhatsApp Catalog
     * https://developers.facebook.com/docs/commerce-platform/catalog/overview
     */
    public function exportCatalog()
    {
        $products  = Product::active()->with(['images', 'category'])->get();
        $siteName  = Setting::get('site_name', config('app.name'));
        $siteUrl   = rtrim(config('app.url'), '/');

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="catalogue-whatsapp-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($products, $siteUrl, $siteName) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 pour compatibilité Excel / Meta
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // En-têtes colonnes Meta Commerce
            fputcsv($handle, [
                'id', 'title', 'description', 'availability', 'condition',
                'price', 'link', 'image_link', 'brand', 'google_product_category',
                'sale_price', 'identifier_exists',
            ]);

            foreach ($products as $product) {
                $primaryImage = $product->images->where('is_primary', true)->first()
                    ?? $product->images->first();

                $imageUrl = $primaryImage
                    ? $siteUrl . '/storage/' . $primaryImage->path
                    : '';

                $availability = $product->is_in_stock ? 'in stock' : 'out of stock';

                $priceFormatted     = number_format($product->sale_price, 2, '.', '') . ' XOF';
                $comparePriceFormatted = $product->compare_price
                    ? number_format($product->compare_price, 2, '.', '') . ' XOF'
                    : '';

                fputcsv($handle, [
                    $product->id,
                    $product->name,
                    Str::limit(strip_tags($product->short_description ?? $product->description ?? ''), 200),
                    $availability,
                    'new',
                    $comparePriceFormatted ?: $priceFormatted,
                    $siteUrl . '/boutique/' . $product->slug,
                    $imageUrl,
                    $siteName,
                    $product->category?->name ?? '',
                    $priceFormatted,
                    $product->barcode ? 'TRUE' : 'FALSE',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Génère un lien WhatsApp de partage pour un produit
     */
    public function productLink(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'message'    => 'nullable|string|max:500',
        ]);

        $product   = Product::findOrFail($validated['product_id']);
        $waNumber  = preg_replace('/\D/', '', Setting::get('social_whatsapp', ''));
        $productUrl = route('shop.product', $product->slug);

        $defaultMessage = "Bonjour, je souhaite commander :\n*{$product->name}*\nPrix : " .
            number_format($product->sale_price, 0, ',', ' ') . " F CFA\n{$productUrl}";

        $message = $validated['message'] ?? $defaultMessage;
        $waUrl   = "https://wa.me/{$waNumber}?text=" . urlencode($message);

        return response()->json(['url' => $waUrl, 'number' => $waNumber]);
    }
}
