<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WishlistController extends Controller
{
    public function index()
    {
        $customer = auth()->user()->customer;
        if (!$customer) {
            return redirect()->route('login')->with('error', 'Vous devez être un client pour avoir des favoris.');
        }

        $wishlistItems = Wishlist::where('customer_id', $customer->id)
            ->with(['product.images', 'product.category'])
            ->latest()
            ->paginate(12);

        $wishlistData = [
            'data' => $wishlistItems->map(function ($item) {
                $product = $item->product;
                if (!$product) return null;
                $primaryImage = $product->images->where('is_primary', true)->first()
                    ?? $product->images->first();
                return [
                    'id'            => $product->id,
                    'name'          => $product->name,
                    'slug'          => $product->slug,
                    'price'         => $product->sale_price,
                    'compare_price' => $product->compare_price,
                    'stock'         => $product->stock_quantity,
                    'has_variants'  => $product->variants()->exists(),
                    'category_name' => $product->category?->name,
                    'primary_image' => $primaryImage?->path,
                ];
            })->filter()->values()->toArray(),
            'current_page' => $wishlistItems->currentPage(),
            'last_page'    => $wishlistItems->lastPage(),
            'total'        => $wishlistItems->total(),
        ];

        return Inertia::render('Account/Wishlist', [
            'wishlist' => $wishlistData,
        ]);
    }

    public function toggle(Product $product, Request $request)
    {
        $customer = auth()->user()->customer ?? null;
        
        if (!$customer) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Veuillez vous connecter pour ajouter aux favoris.'], 401);
            }
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour gérer vos favoris.');
        }

        $variantId = $request->input('variant_id');
        $variant = $variantId ? \App\Models\ProductVariant::find($variantId) : null;

        $added = Wishlist::toggle($customer, $product, $variant);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'added' => $added,
                'message' => $added ? 'Produit ajouté aux favoris.' : 'Produit retiré des favoris.'
            ]);
        }

        return back()->with('success', $added ? 'Produit ajouté à vos favoris.' : 'Produit retiré de vos favoris.');
    }
}
