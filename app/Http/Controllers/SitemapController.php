<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $products   = Product::where('status', 'active')->select(['slug', 'updated_at'])->get();
        $categories = Category::where('is_active', true)->select(['slug', 'updated_at'])->get();

        $content = view('sitemap', compact('products', 'categories'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=86400'); // cache 24h
    }
}
