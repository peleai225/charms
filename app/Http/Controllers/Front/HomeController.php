<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil
     */
    public function index()
    {
        // Catégories mises en avant
        $featuredCategories = Category::active()
            ->featured()
            ->roots()
            ->ordered()
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            ->take(6)
            ->get();

        // Produits mis en avant
        $eagerLoads = ['images', 'category', 'variants.attributeValues.attribute'];

        $featuredProducts = Product::active()
            ->featured()
            ->with($eagerLoads)
            ->take(8)
            ->get();

        // Nouveautés
        $newProducts = Product::active()
            ->new()
            ->with($eagerLoads)
            ->latest()
            ->take(8)
            ->get();

        // Promotions (produits avec compare_price)
        $saleProducts = Product::active()
            ->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'sale_price')
            ->with($eagerLoads)
            ->take(8)
            ->get();

        return view('front.home', compact(
            'featuredCategories',
            'featuredProducts',
            'newProducts',
            'saleProducts'
        ));
    }
}

