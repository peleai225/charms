<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;

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
        $featuredProducts = Product::active()
            ->featured()
            ->with(['images', 'category'])
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

        return view('front.home', compact(
            'featuredCategories',
            'featuredProducts',
            'newProducts',
            'saleProducts',
            'activeCoupons'
        ));
    }
}

