<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'product_variant_id',
    ];

    // ========== RELATIONS ==========

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // ========== STATIC METHODS ==========

    public static function toggle(Customer $customer, Product $product, ?ProductVariant $variant = null): bool
    {
        $existing = static::where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return false; // Removed
        }

        static::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
        ]);

        return true; // Added
    }

    public static function isInWishlist(Customer $customer, Product $product, ?ProductVariant $variant = null): bool
    {
        return static::where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->exists();
    }
}

