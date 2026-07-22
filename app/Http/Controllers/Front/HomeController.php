<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil
     */
    public function index()
    {
        // Catégories mises en avant (+ comptage produits actifs, prix mini par catégorie)
        $featuredCategories = Category::active()
            ->featured()
            ->roots()
            ->ordered()
            ->take(6)
            ->withCount(['products' => function ($query) {
                $query->active();
            }])
            ->get();

        if ($featuredCategories->isNotEmpty()) {
            $minByCategory = Product::active()
                ->whereIn('category_id', $featuredCategories->pluck('id'))
                ->groupBy('category_id')
                ->selectRaw('category_id, MIN(sale_price) as min_sale_price')
                ->pluck('min_sale_price', 'category_id');

            $featuredCategories->each(function (Category $category) use ($minByCategory) {
                $category->setAttribute(
                    'min_product_price',
                    $minByCategory[$category->id] ?? null
                );
            });
        }

        // Produits mis en avant — avec fallback vers les plus récents si aucun marqué
        // Ordre: par date de mise à jour décroissante (les plus récemment mis en vedette en premier)
        $featuredProducts = Product::active()
            ->featured()
            ->with(['images', 'category'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        // Fallback : si aucun produit n'est marqué "featured", prendre les 8 plus récents actifs
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::active()
                ->with(['images', 'category'])
                ->latest()
                ->take(8)
                ->get();
        }

        // Nouveautés
        $newProducts = Product::active()
            ->new()
            ->with(['images', 'category'])
            ->latest()
            ->take(8)
            ->get();

        // Promotions (produits avec compare_price)
        $saleProducts = Product::active()
            ->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'sale_price')
            ->with(['images', 'category'])
            ->take(8)
            ->get();

        $activeCoupons = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where('first_order_only', false)
            ->whereNull('applicable_products')
            ->take(3)
            ->get();

        // Avis clients approuvés (4-5 étoiles, avec customer)
        $reviews = Review::where('status', Review::STATUS_APPROVED)
            ->where('rating', '>=', 4)
            ->with('customer')
            ->latest()
            ->take(4)
            ->get();

        $reviewStats = Review::where('status', Review::STATUS_APPROVED)->count() > 0
            ? [
                'count' => Review::where('status', Review::STATUS_APPROVED)->count(),
                'avg'   => round(Review::where('status', Review::STATUS_APPROVED)->avg('rating'), 1),
            ]
            : null;

        $formatProduct = function ($product) {
            return [
                'id'            => $product->id,
                'name'          => $product->name,
                'slug'          => $product->slug,
                'price'         => $product->sale_price,
                'compare_price' => $product->compare_price,
                'stock'         => $product->stock_quantity,
                'has_variants'  => $product->variants_count > 0 ?? false,
                'is_new'        => (bool) $product->is_new,
                'category_name' => $product->category?->name,
                'primary_image' => $product->images->where('is_primary', true)->first()?->path
                    ?? $product->images->first()?->path,
            ];
        };

        $categories = $featuredCategories->map(function ($category) {
            return [
                'id'                => $category->id,
                'name'              => $category->name,
                'slug'              => $category->slug,
                'image'             => $category->image,
                'products_count'    => $category->products_count,
                'min_product_price' => $category->min_product_price,
            ];
        });

        $reviewsData = $reviews->map(function ($review) {
            return [
                'id'           => $review->id,
                'rating'       => $review->rating,
                'body'         => $review->body,
                'author'       => $review->customer
                    ? $review->customer->first_name . ' ' . mb_substr($review->customer->last_name, 0, 1) . '.'
                    : 'Client',
                'created_at'   => $review->created_at->diffForHumans(),
            ];
        });

        $whatsapp = \App\Models\Setting::get('social_whatsapp');
        $whatsappNumber = $whatsapp ? preg_replace('/\D/', '', $whatsapp) : null;

        return Inertia::render('HomeTest', [
            'featured_categories' => $categories,
            'featured_products'   => $featuredProducts->map($formatProduct),
            'new_products'        => $newProducts->map($formatProduct),
            'sale_products'       => $saleProducts->map($formatProduct),
            'reviews'             => $reviewsData,
            'review_stats'        => $reviewStats,
            'whatsapp_number'     => $whatsappNumber,
        ]);
    }
}

