<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\BundlePricingService;
use App\Services\Storefront\BestSellers;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Page « Meilleures ventes » de la boutique.
 * Les quantités vendues servent au classement mais ne sont jamais envoyées au navigateur.
 */
class BestSellersController extends Controller
{
    public function __invoke(BestSellers $bestSellers): Response
    {
        $pricing = app(BundlePricingService::class);

        return Inertia::render('Shop/BestSellers', [
            'days' => BestSellers::DAYS,
            'products' => $bestSellers->top()->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->sale_price,
                'compare_price' => $p->compare_price,
                'stock' => $p->variants->isNotEmpty() ? $p->variants->sum('stock_quantity') : $p->stock_quantity,
                'allow_backorder' => (bool) $p->allow_backorder,
                'has_variants' => $p->variants->isNotEmpty(),
                'promotion_label' => $pricing->labelForProduct($p),
                'category_name' => $p->category?->name,
                'primary_image' => $p->images->where('is_primary', true)->first()?->path ?? $p->images->first()?->path,
            ])->values()->all(),
        ]);
    }
}
