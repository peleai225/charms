<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Storefront\FlashSaleShowcase;
use Inertia\Inertia;

/**
 * Page publique /ventes-flash : toutes les ventes en cours et leurs produits.
 */
class FlashSaleController extends Controller
{
    public function index(FlashSaleShowcase $showcase)
    {
        // Même carte que l'accueil (ProductCard) ; prix et prix barré sont posés par le service
        $formatProduct = fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'price' => $p->sale_price,
            'compare_price' => $p->compare_price,
            'stock' => $p->variants->isNotEmpty() ? $p->variants->sum('stock_quantity') : $p->stock_quantity,
            'allow_backorder' => (bool) $p->allow_backorder,
            'has_variants' => $p->variants->isNotEmpty(),
            'category_name' => $p->category?->name,
            'primary_image' => $p->images->where('is_primary', true)->first()?->path
                ?? $p->images->first()?->path,
        ];

        return Inertia::render('Shop/FlashSales', [
            'sales' => $showcase->all($formatProduct),
        ]);
    }
}
