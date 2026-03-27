<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $customer = auth()->user()->customer;
        if (!$customer) {
            return redirect()->route('login')->with('error', 'Vous devez être un client pour avoir des favoris.');
        }

        $wishlistItems = Wishlist::where('customer_id', $customer->id)
            ->with(['product.images', 'variant.attributeValues.attribute'])
            ->latest()
            ->paginate(12);

        return view('front.account.wishlist', compact('wishlistItems'));
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
