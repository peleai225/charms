<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Caisse en magasin. Les ventes flash en cours s'appliquent comme sur le site (FlashSalePricing) :
 * chaque ligne garde son prix normal, et le prix facturé est recalculé à chaque lecture du panier
 * et à l'encaissement, pour qu'une vente terminée ne soit jamais facturée au prix flash.
 */
class PosService
{
    public function __construct(private readonly FlashSalePricing $flash) {}

    /**
     * Rechercher un produit ou variante par code-barres / SKU.
     *
     * @return array{found: bool, type?: string, data?: array, message?: string}
     */
    public function scan(string $code): array
    {
        $product = Product::where('barcode', $code)
            ->orWhere('sku', $code)
            ->first();

        if ($product) {
            return [
                'found' => true,
                'type' => 'product',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    ...$this->priceData($product),
                    'stock' => $product->stock_quantity,
                    'image' => $product->primary_image_url,
                    'has_variants' => $product->variants()->count() > 0,
                ],
            ];
        }

        $variant = ProductVariant::where('barcode', $code)->orWhere('sku', $code)->with('product')->first();

        if ($variant) {
            return [
                'found' => true,
                'type' => 'variant',
                'data' => [
                    'id' => $variant->product_id,
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'name' => $variant->product->name,
                    'variant_name' => $variant->name,
                    'sku' => $variant->sku,
                    ...$this->priceData($variant->product, $variant),
                    'stock' => $variant->stock_quantity,
                ],
            ];
        }

        return [
            'found' => false,
            'message' => 'Produit non trouvé pour le code: ' . $code,
        ];
    }

    /**
     * Ajouter un article au panier session.
     *
     * @return array Résumé du panier mis à jour
     */
    public function addToCart(int $productId, ?int $variantId, int $quantity = 1): array
    {
        $cart = session('pos_cart', []);
        $key = $productId . '-' . ($variantId ?? 0);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $product = Product::findOrFail($productId);
            $variant = $variantId ? ProductVariant::find($variantId) : null;

            $cart[$key] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name' => $product->name,
                'variant_name' => $variant?->name,
                'sku' => $variant?->sku ?? $product->sku,
                // Prix normal figé à l'ajout ; « price » (facturé) est recalculé selon les ventes flash
                'regular_price' => $this->amount($this->flash->regularPrice($product, $variant)),
                'price' => $this->amount($this->flash->regularPrice($product, $variant)),
                'quantity' => $quantity,
                'image' => $product->primary_image_url,
            ];
        }

        session(['pos_cart' => $cart]);

        return $this->getCartSummary();
    }

    /**
     * Prix à afficher et à facturer pour un produit (ou une variante), vente flash comprise.
     *
     * @return array{price: int|float, price_formatted: string, regular_price: int|float, flash: ?array}
     */
    private function priceData(Product $product, ?ProductVariant $variant = null): array
    {
        [$price, $sale] = $this->flash->unitPrice($product, $variant);

        return [
            'price' => $this->amount($price),
            'price_formatted' => format_price($price),
            'regular_price' => $this->amount($this->flash->regularPrice($product, $variant)),
            'flash' => $sale ? [
                'id' => $sale->id,
                'name' => $sale->name,
                'discount_percent' => $sale->discount_percent,
                'ends_at' => $sale->ends_at->toIso8601String(),
            ] : null,
        ];
    }

    /**
     * Aligne les lignes du panier sur les ventes flash en cours (même règle que le panier du site) :
     * prix flash si une vente couvre le produit maintenant, sinon prix normal.
     */
    private function syncFlashPrices(): array
    {
        $cart = session('pos_cart', []);
        if (! $cart) {
            return $cart;
        }

        $sales = $this->flash->bestSales(array_column($cart, 'product_id'));

        foreach ($cart as $key => $item) {
            // Panier ouvert avant cette version : le prix enregistré était le prix normal
            $regular = (float) ($item['regular_price'] ?? $item['price']);
            $sale = $sales[$item['product_id']] ?? null;

            $cart[$key]['regular_price'] = $this->amount($regular);
            $cart[$key]['price'] = $this->amount($sale ? $sale->priceFor($regular) : $regular);
            $cart[$key]['flash_sale_id'] = $sale?->id;
            $cart[$key]['flash_label'] = $sale ? "Vente flash -{$sale->discount_percent} %" : null;
        }

        session(['pos_cart' => $cart]);

        return $cart;
    }

    // Les montants en F CFA sont entiers : 8000 plutôt que 8000.0 ou « 8000.00 »
    private function amount(float|int|string $value): int|float
    {
        $value = (float) $value;

        return floor($value) === $value ? (int) $value : $value;
    }

    /**
     * Mettre à jour la quantité d'un article (supprime si quantité <= 0).
     *
     * @return array Résumé du panier mis à jour
     */
    public function updateCartItem(string $key, int $quantity): array
    {
        $cart = session('pos_cart', []);

        if (isset($cart[$key])) {
            if ($quantity <= 0) {
                unset($cart[$key]);
            } else {
                $cart[$key]['quantity'] = $quantity;
            }
            session(['pos_cart' => $cart]);
        }

        return $this->getCartSummary();
    }

    /**
     * Supprimer un article du panier.
     *
     * @return array Résumé du panier mis à jour
     */
    public function removeCartItem(string $key): array
    {
        $cart = session('pos_cart', []);
        unset($cart[$key]);
        session(['pos_cart' => $cart]);

        return $this->getCartSummary();
    }

    /**
     * Vider le panier.
     *
     * @return array Résumé du panier vide
     */
    public function clearCart(): array
    {
        session()->forget('pos_cart');

        return $this->getCartSummary();
    }

    /**
     * Résumé du panier courant (items, count, totaux).
     */
    public function getCartSummary(): array
    {
        $cart = $this->syncFlashPrices();
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

    /**
     * Finaliser une vente POS : crée la commande, décrémente le stock, vide le panier.
     *
     * @param  array{payment_method: string, amount_received: float|null, customer_id: int|null}  $data
     * @return array{order: Order, change: float, receipt_url: string}
     *
     * @throws \Exception si le panier est vide ou si la transaction échoue
     */
    public function checkout(array $data): array
    {
        // Prix facturés = ventes flash en cours au moment de l'encaissement
        $cart = $this->syncFlashPrices();

        if (empty($cart)) {
            throw new \RuntimeException('Le panier est vide');
        }

        DB::beginTransaction();

        try {
            $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

            $order = Order::create([
                'order_number' => 'POS-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                'customer_id' => $data['customer_id'] ?? null,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'payment_method' => $data['payment_method'],
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

                $this->decrementStock($item, $order);
            }

            session()->forget('pos_cart');

            DB::commit();

            $amountReceived = 0;
            $change = 0;
            if ($data['payment_method'] === 'cash' && !empty($data['amount_received'])) {
                $amountReceived = (float) $data['amount_received'];
                $change = $amountReceived - $subtotal;
            }

            $receiptParams = http_build_query([
                'auto_print' => 1,
                'change' => max(0, $change),
                'amount_received' => $amountReceived ?: $subtotal,
            ]);

            return [
                'order' => $order,
                'change' => $change,
                'change_formatted' => format_price(max(0, $change)),
                'receipt_url' => route('admin.scanner.receipt', ['order' => $order->id]) . '?' . $receiptParams,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS checkout error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mouvement de stock rapide via scanner.
     */
    public function stockMovement(int $productId, ?int $variantId, string $type, int $quantity, ?string $notes = null): StockMovement
    {
        $product = Product::findOrFail($productId);
        $variant = $variantId ? ProductVariant::find($variantId) : null;

        $signedQuantity = $type === 'out' ? -$quantity : $quantity;

        return StockMovement::createMovement(
            product: $product,
            type: $type,
            quantity: $signedQuantity,
            variant: $variant,
            notes: $notes ?? 'Mouvement via scanner'
        );
    }

    /**
     * Décrémenter le stock d'un article de panier après vente.
     */
    private function decrementStock(array $item, Order $order): void
    {
        $product = Product::find($item['product_id']);
        if (!$product || !$product->track_stock) {
            return;
        }

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
