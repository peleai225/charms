<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'customer_id',
        'coupon_code',
    ];

    // ========== RELATIONS ==========

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    // ========== ACCESSORS ==========

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->unit_price * $item->quantity;
        });
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getDiscountAmountAttribute(): float
    {
        if (!$this->coupon_code || !$this->coupon) {
            return 0;
        }

        return $this->coupon->calculateDiscount($this->subtotal);
    }

    public function getTotalAttribute(): float
    {
        return max(0, $this->subtotal - $this->discount_amount);
    }

    public function getIsEmptyAttribute(): bool
    {
        return $this->items->isEmpty();
    }

    // ========== METHODS ==========

    public static function getOrCreate(?string $sessionId = null, ?Customer $customer = null): self
    {
        $sessionId = $sessionId ?? session()->getId();

        // Chercher un panier existant
        $cart = static::where('session_id', $sessionId)
            ->orWhere(function ($query) use ($customer) {
                if ($customer) {
                    $query->where('customer_id', $customer->id);
                }
            })
            ->first();

        if ($cart) {
            // Fusionner si nécessaire
            if ($customer && !$cart->customer_id) {
                $cart->update(['customer_id' => $customer->id]);
            }
            return $cart;
        }

        // Créer un nouveau panier
        return static::create([
            'session_id' => $sessionId,
            'customer_id' => $customer?->id,
        ]);
    }

    public function addItem(Product $product, int $quantity = 1, ?ProductVariant $variant = null): CartItem
    {
        $existingItem = $this->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($existingItem) {
            $newQty = $existingItem->quantity + $quantity;
            $existingItem->update(['quantity' => $newQty]);
            $this->recalcBulkPrices($product->id);
            return $existingItem->fresh();
        }

        // Prix initial : sera recalculé juste après par recalcBulkPrices
        $price = (float) $product->sale_price;

        $item = $this->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => $quantity,
            'unit_price' => $price,
        ]);

        $this->recalcBulkPrices($product->id);
        return $item->fresh();
    }

    public function updateItemQuantity(int $itemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $item = $this->items()->find($itemId);
            $productId = $item?->product_id;
            $this->items()->where('id', $itemId)->delete();
            if ($productId) {
                $this->recalcBulkPrices($productId);
            }
        } else {
            $item = $this->items()->with('product')->find($itemId);
            if ($item) {
                $item->update(['quantity' => $quantity]);
                $this->recalcBulkPrices($item->product_id);
            }
        }
    }

    protected function getTotalProductQuantity(int $productId): int
    {
        return (int) $this->items()->where('product_id', $productId)->sum('quantity');
    }

    protected function recalcBulkPrices(int $productId): void
    {
        $items = $this->items()->where('product_id', $productId)->with('product')->get();
        if ($items->isEmpty()) {
            return;
        }

        $product = $items->first()->product;

        // Si le produit a ses propres règles → agrégation par produit (logique existante)
        if ($product->hasOwnBulkPricingRules()) {
            $totalQty = $items->sum('quantity');
            $bulkPrice = $product->getBulkUnitPrice($totalQty);

            foreach ($items as $item) {
                if ($item->unit_price != $bulkPrice) {
                    $item->update(['unit_price' => $bulkPrice]);
                }
            }
        } else {
            // Pas de règles propres → déléguer à l'agrégation catégorie
            $this->recalcCategoryBulkPrices();
        }
    }

    /**
     * Recalcule les prix bulk par catégorie pour les produits sans règles propres.
     *
     * Logique :
     *   1. Sélectionner les items dont le produit n'a PAS de bulk_pricing_rules propres
     *   2. Regrouper par (category_id + sale_price arrondi à l'entier)
     *   3. Pour chaque groupe, calculer la quantité totale et appliquer le prix catégorie
     */
    protected function recalcCategoryBulkPrices(): void
    {
        $items = $this->items()->with('product.category')->get();

        // Ne garder que les items dont le produit n'a PAS ses propres règles
        $categoryItems = $items->filter(function ($item) {
            return !$item->product->hasOwnBulkPricingRules();
        });

        if ($categoryItems->isEmpty()) {
            return;
        }

        // Grouper par (category_id + sale_price arrondi)
        $groups = $categoryItems->groupBy(function ($item) {
            $categoryId = $item->product->category_id ?? 0;
            $roundedPrice = round((float) $item->product->sale_price);
            return $categoryId . '_' . $roundedPrice;
        });

        foreach ($groups as $group) {
            $firstItem = $group->first();
            $category = $firstItem->product->category;
            $basePrice = (float) $firstItem->product->sale_price;

            if (!$category || empty($category->bulk_pricing_rules)) {
                // Pas de catégorie ou pas de règles catégorie → prix standard
                foreach ($group as $item) {
                    if ($item->unit_price != $basePrice) {
                        $item->update(['unit_price' => $basePrice]);
                    }
                }
                continue;
            }

            $totalQty = $group->sum('quantity');
            $bulkPrice = $category->getBulkUnitPrice($totalQty, $basePrice);

            foreach ($group as $item) {
                if ($item->unit_price != $bulkPrice) {
                    $item->update(['unit_price' => $bulkPrice]);
                }
            }
        }
    }

    public function removeItem(int $itemId): void
    {
        $item = $this->items()->find($itemId);
        $productId = $item?->product_id;
        $this->items()->where('id', $itemId)->delete();
        if ($productId) {
            $this->recalcBulkPrices($productId);
        }
    }

    public function clear(): void
    {
        $this->items()->delete();
        $this->update(['coupon_code' => null]);
    }

    public function applyCoupon(string $code): bool
    {
        $coupon = Coupon::where('code', Str::upper($code))->valid()->first();

        if (!$coupon) {
            return false;
        }

        $customer = $this->customer;
        $validation = $coupon->canBeUsedBy($customer, $this->subtotal);
        
        if (!$validation['valid']) {
            return false;
        }

        $this->update(['coupon_code' => $coupon->code]);
        return true;
    }

    public function removeCoupon(): void
    {
        $this->update(['coupon_code' => null]);
    }
}

