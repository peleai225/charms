<?php

namespace App\Models;

use App\Services\BundlePricingService;
use App\Support\CartPricing;
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

    protected ?CartPricing $pricingCache = null;

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

    // ========== TARIFICATION ==========

    /**
     * Tarification du panier, mémoïsée par instance. Toute mutation du panier
     * appelle forgetPricing().
     */
    public function pricing(): CartPricing
    {
        return $this->pricingCache ??= app(BundlePricingService::class)->price($this);
    }

    protected function forgetPricing(): void
    {
        $this->pricingCache = null;
    }

    // ========== ACCESSORS ==========

    /**
     * Total catalogue, avant toute remise. Attention : avant l'introduction des
     * offres par lot, cet accesseur rendait un montant déjà remisé. Les
     * consommateurs qui raisonnent sur ce que le client paie doivent lire
     * payable_subtotal.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->pricing()->subtotal();
    }

    public function getBundleDiscountAttribute(): float
    {
        return $this->pricing()->bundleDiscount();
    }

    public function getPayableSubtotalAttribute(): float
    {
        return $this->pricing()->payableSubtotal();
    }

    public function getDiscountAmountAttribute(): float
    {
        return $this->pricing()->couponDiscount;
    }

    public function getTotalAttribute(): float
    {
        return $this->pricing()->total();
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
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
            if ($customer && ! $cart->customer_id) {
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
            $existingItem->update(['quantity' => $existingItem->quantity + $quantity]);
            $this->forgetPricing();

            return $existingItem->fresh();
        }

        // Prix catalogue figé à l'ajout. Le prix de la variante prime sur celui
        // du produit, sans quoi une taille au tarif différent sortirait des
        // fourchettes d'offres sans raison visible.
        $item = $this->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => $quantity,
            'unit_price' => (float) ($variant?->sale_price ?? $product->sale_price),
        ]);

        $this->forgetPricing();

        return $item->fresh();
    }

    public function updateItemQuantity(int $itemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->items()->where('id', $itemId)->delete();
        } else {
            $this->items()->where('id', $itemId)->update(['quantity' => $quantity]);
        }

        $this->forgetPricing();
    }

    public function removeItem(int $itemId): void
    {
        $this->items()->where('id', $itemId)->delete();
        $this->forgetPricing();
    }

    public function clear(): void
    {
        $this->items()->delete();
        $this->update(['coupon_code' => null]);
        $this->forgetPricing();
    }

    public function applyCoupon(string $code): bool
    {
        $coupon = Coupon::where('code', Str::upper($code))->valid()->first();

        if (! $coupon) {
            return false;
        }

        $customer = $this->customer;
        $validation = $coupon->canBeUsedBy($customer, $this->payable_subtotal);

        if (! $validation['valid']) {
            return false;
        }

        $this->update(['coupon_code' => $coupon->code]);
        $this->forgetPricing();

        return true;
    }

    public function removeCoupon(): void
    {
        $this->update(['coupon_code' => null]);
        $this->forgetPricing();
    }
}
