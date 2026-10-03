<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\BundlePricingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * Affiche le panier
     */
    public function index()
    {
        $cart = $this->getCart();
        $cart->load(['items.product.images', 'items.product.category', 'items.variant.attributeValues.attribute', 'coupon']);

        $pricing = $cart->pricing();

        // Format cart data for Inertia
        $cartData = [
            'id' => $cart->id,
            'items' => $cart->items->map(function ($item) use ($pricing) {
                $primaryImage = $item->product->images->where('is_primary', true)->first()
                    ?? $item->product->images->first();

                $variantAttributes = [];
                if ($item->variant) {
                    foreach ($item->variant->attributeValues as $attrValue) {
                        $variantAttributes[] = [
                            'name' => $attrValue->attribute->name,
                            'value' => $attrValue->value,
                        ];
                    }
                }

                $line = $pricing->lineFor($item);

                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->unit_price * $item->quantity,
                    'discount' => $line->discount,
                    'line_total' => $line->lineTotal(),
                    'promotion' => $line->promotion?->name,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'slug' => $item->product->slug,
                        'primary_image' => $primaryImage?->path,
                    ],
                    'variant' => $item->variant ? [
                        'id' => $item->variant->id,
                        'attributes' => $variantAttributes,
                    ] : null,
                ];
            })->toArray(),
            'subtotal' => $pricing->subtotal(),
            'bundle_discount' => $pricing->bundleDiscount(),
            'discount' => $pricing->couponDiscount,
            'coupon_base' => $pricing->couponEligibleBase(),
            'shipping_cost' => $cart->shipping_cost ?? 0,
            'total' => $pricing->total(),
            'coupon_code' => $cart->coupon_code,
        ];

        // Offres à portée de main, une entrée par promotion incomplète
        $nudges = app(BundlePricingService::class)->nudges($cart);

        return Inertia::render('Cart/Index', [
            'cart' => $cartData,
            'nudges' => $nudges,
        ]);
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
        if ($stockAvailable < $request->quantity && ! $product->allow_backorder) {
            return back()->with('error', 'Stock insuffisant.');
        }

        // Ajouter au panier
        $cart = $this->getCart();
        $cart->addItem($product, $request->quantity, $variant);

        // Inertia envoie X-Inertia: true → retourner redirect (pas JSON)
        if ($request->header('X-Inertia')) {
            return back()->with('success', 'Produit ajouté au panier !');
        }

        // Fetch brut (ProductCard, etc.) → JSON
        if ($request->wantsJson() || $request->ajax()) {
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
        $item = $cart->items()->with(['product', 'variant'])->findOrFail($itemId);

        if ($request->quantity > 0) {
            $stock = $item->variant
                ? $item->variant->stock_quantity
                : $item->product->stock_quantity;
            $allowBackorder = $item->product->allow_backorder ?? false;
            if (! $allowBackorder && $request->quantity > $stock) {
                $msg = "Stock insuffisant (max {$stock}).";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return back()->withErrors(['quantity' => $msg]);
            }
        }

        $cart->updateItemQuantity($itemId, $request->quantity);

        // Fetch pur (pas Inertia) → JSON pour mise à jour réactive sans rechargement
        if (! $request->header('X-Inertia') && ($request->wantsJson() || $request->ajax())) {
            $cart->refresh();

            return response()->json($this->summaryPayload($cart));
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

        if (! request()->header('X-Inertia') && (request()->wantsJson() || request()->ajax())) {
            $cart->refresh();

            return response()->json($this->summaryPayload($cart));
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
        try {
            $request->validate([
                'coupon_code' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Le code promo est requis.',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $cart = $this->getCart();

        // Vérifier le coupon manuellement pour avoir un message d'erreur détaillé
        $coupon = \App\Models\Coupon::where('code', \Illuminate\Support\Str::upper($request->coupon_code))->first();

        if (! $coupon) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Code promo introuvable.',
                ], 400);
            }

            return back()->with('error', 'Code promo introuvable.');
        }

        $customer = $cart->customer;
        // Le minimum de commande se mesure sur ce que le client paie, remises de
        // lot déduites, et non sur le total catalogue.
        $validation = $coupon->canBeUsedBy($customer, $cart->payable_subtotal);

        if (! $validation['valid']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $validation['message'],
                ], 400);
            }

            return back()->with('error', $validation['message']);
        }

        if ($cart->applyCoupon($request->coupon_code)) {
            $cart->refresh();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(array_merge($this->summaryPayload($cart), [
                    'message' => 'Code promo appliqué ! Réduction de '.format_price($cart->discount_amount),
                    'discount_amount' => $cart->discount_amount,
                    'coupon_code' => $cart->coupon_code,
                ]));
            }

            return back()->with('success', 'Code promo appliqué ! Réduction de '.format_price($cart->discount_amount));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Impossible d\'appliquer le code promo.',
            ], 400);
        }

        return back()->with('error', 'Impossible d\'appliquer le code promo.');
    }

    /**
     * Retirer le code promo
     */
    public function removeCoupon()
    {
        $cart = $this->getCart();
        $cart->removeCoupon();

        if (request()->wantsJson() || request()->ajax()) {
            $cart->refresh();

            return response()->json($this->summaryPayload($cart));
        }

        return back()->with('success', 'Code promo retiré.');
    }

    /**
     * Récapitulatif renvoyé aux mises à jour AJAX du panier.
     *
     * La remise de lot dépend du panier entier : changer la quantité d'une ligne
     * peut compléter ou défaire un lot sur une autre. Le front ne peut donc pas
     * la recalculer seul — il reçoit ici les remises de toutes les lignes.
     */
    protected function summaryPayload(Cart $cart): array
    {
        $pricing = $cart->pricing();

        return [
            'success' => true,
            'subtotal' => $pricing->subtotal(),
            'bundle_discount' => $pricing->bundleDiscount(),
            'discount' => $pricing->couponDiscount,
            'coupon_base' => $pricing->couponEligibleBase(),
            'total' => $pricing->total(),
            'cart_count' => $cart->items_count,
            'lines' => collect($pricing->lines)->map(fn ($line) => [
                'id' => $line->cartItemId,
                'discount' => $line->discount,
                'line_total' => $line->lineTotal(),
                'promotion' => $line->promotion?->name,
            ])->values()->all(),
        ];
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
     * Retourne le contenu du panier en JSON pour le drawer
     */
    public function drawer()
    {
        $cart = $this->getCart();
        $cart->load(['items.product.images', 'items.variant.attributeValues.attribute', 'coupon']);

        $items = $cart->items->map(function ($item) {
            $image = $item->product->images->where('is_primary', true)->first()
                ?? $item->product->images->first();

            $originalPrice = $item->product_variant_id
                ? ($item->variant?->effective_price ?? $item->product->sale_price)
                : $item->product->sale_price;
            $isBulk = $item->unit_price < $originalPrice;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'slug' => $item->product->slug,
                'image' => $image ? asset('storage/'.$image->path) : null,
                'price' => $item->unit_price,
                'price_fmt' => number_format($item->unit_price, 0, ',', ' ').' F CFA',
                'original_price_fmt' => $isBulk ? number_format($originalPrice, 0, ',', ' ').' F CFA' : null,
                'quantity' => $item->quantity,
                'subtotal_fmt' => number_format($item->unit_price * $item->quantity, 0, ',', ' ').' F CFA',
                'variant_id' => $item->product_variant_id,
                'variant' => $item->variant
                    ? $item->variant->attributeValues->pluck('value')->implode(' / ')
                    : null,
                'update_url' => route('cart.update', $item->id),
                'remove_url' => route('cart.remove', $item->id),
            ];
        });

        $pricing = $cart->pricing();

        return response()->json([
            'items' => $items,
            'count' => $cart->items_count,
            'subtotal_fmt' => number_format($pricing->subtotal(), 0, ',', ' ').' F CFA',
            // Sans cette ligne, le drawer afficherait 12 000 de sous-total et
            // 10 000 de total sans rien pour expliquer l'écart.
            'bundle_discount_fmt' => $pricing->bundleDiscount() > 0
                ? number_format($pricing->bundleDiscount(), 0, ',', ' ').' F CFA'
                : null,
            'discount_fmt' => $pricing->couponDiscount > 0
                ? number_format($pricing->couponDiscount, 0, ',', ' ').' F CFA'
                : null,
            'total_fmt' => number_format($pricing->total(), 0, ',', ' ').' F CFA',
            'coupon_code' => $cart->coupon_code,
            'checkout_url' => route('checkout.index'),
        ]);
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
