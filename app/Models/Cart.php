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

        $totalProductQty = $this->getTotalProductQuantity($product->id) + $quantity;
        $price = $product->getBulkUnitPrice($totalProductQty);

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

        $totalQty = $items->sum('quantity');
        $product = $items->first()->product;
        $bulkPrice = $product->getBulkUnitPrice($totalQty);

        foreach ($items as $item) {
            if ($item->unit_price != $bulkPrice) {
                $item->update(['unit_price' => $bulkPrice]);
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

