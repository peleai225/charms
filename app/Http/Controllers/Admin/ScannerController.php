<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScannerController extends Controller
{
    /**
     * Page principale du scanner (mode POS/Caisse)
     */
    public function index()
    {
        $receiptAutoPrint = Setting::get('pos_receipt_auto_print', '0') === '1';

        return view('admin.scanner.index', compact('receiptAutoPrint'));
    }

    /**
     * Scanner un code et retourner les infos produit
     */
    public function scan(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = trim($request->code);

        // Chercher dans les produits (par code-barres ou SKU)
        $product = Product::where('barcode', $code)
            ->orWhere('sku', $code)
            ->first();

        if ($product) {
            return response()->json([
                'found' => true,
                'type' => 'product',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => $product->sale_price,
                    'price_formatted' => format_price($product->sale_price),
                    'stock' => $product->stock_quantity,
                    'image' => $product->primary_image_url,
                    'has_variants' => $product->variants()->count() > 0,
                ],
            ]);
        }

        // Chercher dans les variantes
        $variant = ProductVariant::where('sku', $code)->with('product')->first();

        if ($variant) {
            return response()->json([
                'found' => true,
                'type' => 'variant',
                'data' => [
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'name' => $variant->product->name,
                    'variant_name' => $variant->name,
                    'sku' => $variant->sku,
                    'price' => $variant->sale_price ?? $variant->product->sale_price,
                    'price_formatted' => format_price($variant->sale_price ?? $variant->product->sale_price),
                    'stock' => $variant->stock_quantity,
                ],
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'Produit non trouvé pour le code: ' . $code,
        ], 404);
    }

    /**
     * Ajouter un produit au panier POS
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'integer|min:1',
        ]);

        // Stocker en session pour le mode POS
        $cart = session('pos_cart', []);
        
        $key = $request->product_id . '-' . ($request->variant_id ?? 0);
        
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $request->quantity ?? 1;
        } else {
            $product = Product::find($request->product_id);
            $variant = $request->variant_id ? ProductVariant::find($request->variant_id) : null;
            
            $cart[$key] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name' => $product->name,
                'variant_name' => $variant?->name,
                'sku' => $variant?->sku ?? $product->sku,
                'price' => $variant?->sale_price ?? $product->sale_price,
                'quantity' => $request->quantity ?? 1,
                'image' => $product->primary_image_url,
            ];
        }
        
        session(['pos_cart' => $cart]);
        
        return response()->json([
            'success' => true,
            'cart' => $this->getCartSummary(),
        ]);
    }

    /**
     * Récupérer le panier POS
     */
    public function getCart()
    {
        return response()->json($this->getCartSummary());
    }

    /**
     * Mettre à jour quantité dans le panier POS
     */
    public function updateCartItem(Request $request, string $key)
    {
        $cart = session('pos_cart', []);
        
        if (isset($cart[$key])) {
            $quantity = (int) $request->quantity;
            if ($quantity <= 0) {
                unset($cart[$key]);
            } else {
                $cart[$key]['quantity'] = $quantity;
            }
            session(['pos_cart' => $cart]);
        }
        
        return response()->json([
            'success' => true,
            'cart' => $this->getCartSummary(),
        ]);
    }

    /**
     * Supprimer un article du panier POS
     */
    public function removeCartItem(string $key)
    {
        $cart = session('pos_cart', []);
        unset($cart[$key]);
        session(['pos_cart' => $cart]);
        
        return response()->json([
            'success' => true,
            'cart' => $this->getCartSummary(),
        ]);
    }

    /**
     * Vider le panier POS
     */
    public function clearCart()
    {
        session()->forget('pos_cart');
        
        return response()->json([
            'success' => true,
            'cart' => $this->getCartSummary(),
        ]);
    }

    /**
     * Finaliser la vente POS
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,mobile_money',
            'amount_received' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $cart = session('pos_cart', []);
        
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Le panier est vide',
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
            
            // Créer la commande
            $order = Order::create([
                'order_number' => 'POS-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                'customer_id' => $request->customer_id,
                'status' => 'delivered', // Vente POS terminée sur place
                'payment_status' => 'paid',
                'payment_method' => $request->payment_method,
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'shipping_amount' => 0,
                'discount_amount' => 0,
                'total' => $subtotal,
                'source' => 'pos',
                'paid_at' => now(),
                'billing_first_name' => 'Client',
                'billing_last_name' => 'Comptoir',
                'billing_email' => 'pos@magasin.local',
                'billing_address' => 'Vente en magasin',
                'billing_city' => 'Magasin',
                'billing_postal_code' => '00000',
                'billing_country' => 'CI',
                'shipping_first_name' => 'Client',
                'shipping_last_name' => 'Comptoir',
                'shipping_address' => 'Vente en magasin',
                'shipping_city' => 'Magasin',
                'shipping_postal_code' => '00000',
                'shipping_country' => 'CI',
            ]);

            // Créer les lignes de commande et décrémenter le stock
            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'name' => $item['name'],
                    'variant_name' => $item['variant_name'],
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                ]);

                // Décrémenter le stock
                $product = Product::find($item['product_id']);
                if ($product && $product->track_stock) {
                    if ($item['variant_id']) {
                        $variant = ProductVariant::find($item['variant_id']);
                        if ($variant) {
                            StockMovement::createMovement(
                                product: $product,
                                type: StockMovement::TYPE_SALE,
                                quantity: -$item['quantity'],
                                variant: $variant,
                                unitPrice: $item['price'],
                                reference: $order,
                                notes: "Vente POS #{$order->order_number}"
                            );
                        }
                    } else {
                        StockMovement::createMovement(
                            product: $product,
                            type: StockMovement::TYPE_SALE,
                            quantity: -$item['quantity'],
                            unitPrice: $item['price'],
                            reference: $order,
                            notes: "Vente POS #{$order->order_number}"
                        );
                    }
                }
            }

            // Vider le panier
            session()->forget('pos_cart');

            DB::commit();

            $change = 0;
            $amountReceived = 0;
            if ($request->payment_method === 'cash' && $request->amount_received) {
                $amountReceived = (float) $request->amount_received;
                $change = $amountReceived - $subtotal;
            }

            $receiptParams = http_build_query([
                'auto_print' => 1,
                'change' => max(0, $change),
                'amount_received' => $amountReceived ?: $subtotal,
            ]);
            $receiptUrl = route('admin.scanner.receipt', ['order' => $order->id]) . '?' . $receiptParams;

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'total_formatted' => format_price($order->total),
                ],
                'change' => $change,
                'change_formatted' => format_price(max(0, $change)),
                'receipt_url' => $receiptUrl,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('POS checkout error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Afficher le reçu d'une vente POS pour impression
     */
    public function receipt(Order $order)
    {
        // Vérifier que c'est une commande POS
        if ($order->source !== 'pos') {
            abort(404);
        }

        $change = (float) request('change', 0);
        $amountReceived = (float) request('amount_received', $order->total);

        return view('admin.scanner.receipt', compact('order', 'change', 'amountReceived'));
    }

    /**
     * Mouvement de stock rapide via scanner
     */
    public function stockMovement(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);
        $variant = $request->variant_id ? ProductVariant::find($request->variant_id) : null;

        $quantity = $request->type === 'out' ? -$request->quantity : $request->quantity;

        $movement = StockMovement::createMovement(
            product: $product,
            type: $request->type,
            quantity: $quantity,
            variant: $variant,
            notes: $request->notes ?? "Mouvement via scanner"
        );

        return response()->json([
            'success' => true,
            'movement' => [
                'id' => $movement->id,
                'type' => $movement->type,
                'quantity' => $movement->quantity,
                'stock_before' => $movement->stock_before,
                'stock_after' => $movement->stock_after,
            ],
            'new_stock' => $variant ? $variant->fresh()->stock_quantity : $product->fresh()->stock_quantity,
        ]);
    }

    /**
     * Résumé du panier POS
     */
    protected function getCartSummary(): array
    {
        $cart = session('pos_cart', []);
        $items = collect($cart)->values();
        $subtotal = $items->sum(fn($item) => $item['price'] * $item['quantity']);
        $count = $items->sum('quantity');

        return [
            'items' => $items,
            'count' => $count,
            'subtotal' => $subtotal,
            'subtotal_formatted' => format_price($subtotal),
            'total' => $subtotal,
            'total_formatted' => format_price($subtotal),
        ];
    }
}

