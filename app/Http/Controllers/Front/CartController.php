<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Affiche le panier
     */
    public function index()
    {
        $cart = $this->getCart();
        $cart->load(['items.product.images', 'items.variant.attributeValues.attribute']);

        return view('front.cart.index', compact('cart'));
    }

    /**
     * Ajouter un produit au panier
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $product = Product::findOrFail($request->product_id);
        $variant = $request->variant_id ? ProductVariant::find($request->variant_id) : null;

        // Vérifier que le produit est actif
        if ($product->status !== 'active') {
            return back()->with('error', 'Ce produit n\'est plus disponible.');
        }

        // Vérifier le stock
        $stockAvailable = $variant ? $variant->stock_quantity : $product->stock_quantity;
        if ($stockAvailable < $request->quantity && !$product->allow_backorder) {
            return back()->with('error', 'Stock insuffisant.');
        }

        // Ajouter au panier
        $cart = $this->getCart();
        $cart->addItem($product, $request->quantity, $variant);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produit ajouté au panier',
                'cart_count' => $cart->fresh()->items_count,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Produit ajouté au panier !');
    }

    /**
     * Mettre à jour la quantité d'un article
     */
    public function update(Request $request, int $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:99',
        ]);

        $cart = $this->getCart();
        $cart->updateItemQuantity($itemId, $request->quantity);

        if ($request->ajax()) {
            $cart->refresh();
            return response()->json([
                'success' => true,
                'subtotal' => number_format($cart->subtotal, 2, ',', ' ') . ' €',
                'total' => number_format($cart->total, 2, ',', ' ') . ' €',
                'cart_count' => $cart->items_count,
            ]);
        }

        return back()->with('success', 'Panier mis à jour.');
    }

    /**
     * Supprimer un article
     */
    public function remove(int $itemId)
    {
        $cart = $this->getCart();
        $cart->removeItem($itemId);

        if (request()->ajax()) {
            $cart->refresh();
            return response()->json([
                'success' => true,
                'cart_count' => $cart->items_count,
            ]);
        }

        return back()->with('success', 'Article supprimé du panier.');
    }

    /**
     * Vider le panier
     */
    public function clear()
    {
        $cart = $this->getCart();
        $cart->clear();

        return back()->with('success', 'Panier vidé.');
    }

    /**
     * Appliquer un code promo
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $cart = $this->getCart();

        if ($cart->applyCoupon($request->coupon_code)) {
            return back()->with('success', 'Code promo appliqué !');
        }

        return back()->with('error', 'Code promo invalide ou expiré.');
    }

    /**
     * Retirer le code promo
     */
    public function removeCoupon()
    {
        $cart = $this->getCart();
        $cart->removeCoupon();

        return back()->with('success', 'Code promo retiré.');
    }

    /**
     * Récupérer ou créer le panier
     */
    protected function getCart(): Cart
    {
        $customer = null;
        if (auth()->check()) {
            $customer = Customer::where('user_id', auth()->id())->first();
        }

        return Cart::getOrCreate(session()->getId(), $customer);
    }

    /**
     * API : Récupérer le nombre d'articles dans le panier
     */
    public function count()
    {
        $cart = $this->getCart();
        
        return response()->json([
            'count' => $cart->items_count,
        ]);
    }
}

