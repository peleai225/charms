<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    // ========== RELATIONS ==========

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // ========== ACCESSORS ==========

    public function getTotalAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }

    public function getNameAttribute(): string
    {
        $name = $this->product->name;
        if ($this->variant) {
            $name .= ' - ' . $this->variant->name;
        }
        return $name;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->variant?->image_url ?? $this->product->primary_image_url;
    }

    public function getIsInStockAttribute(): bool
    {
        if ($this->variant) {
            return $this->variant->stock_quantity >= $this->quantity || $this->product->allow_backorder;
        }
        return $this->product->stock_quantity >= $this->quantity || $this->product->allow_backorder;
    }

    public function getAvailableStockAttribute(): int
    {
        if ($this->variant) {
            return $this->variant->stock_quantity;
        }
        return $this->product->stock_quantity;
    }
}

