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
            $existingItem->increment('quantity', $quantity);
            return $existingItem->fresh();
        }

        $price = $variant?->effective_price ?? $product->sale_price;

        return $this->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => $quantity,
            'unit_price' => $price,
        ]);
    }

    public function updateItemQuantity(int $itemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->items()->where('id', $itemId)->delete();
        } else {
            $this->items()->where('id', $itemId)->update(['quantity' => $quantity]);
        }
    }

    public function removeItem(int $itemId): void
    {
        $this->items()->where('id', $itemId)->delete();
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

