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
            ->take(6)
            ->get();

        // Produits mis en avant
        $featuredProducts = Product::active()
            ->featured()
            ->with(['images', 'category'])
            ->take(8)
            ->get();

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

        return view('front.home', compact(
            'featuredCategories',
            'featuredProducts',
            'newProducts',
            'saleProducts'
        ));
    }
}

