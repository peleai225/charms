<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'name',
        'sku',
        'variant_name',
        'options',
        'quantity',
        'quantity_shipped',
        'quantity_refunded',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total',
        'cost_price',
    ];

    protected $casts = [
        'options' => 'array',
        'quantity' => 'integer',
        'quantity_shipped' => 'integer',
        'quantity_refunded' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    // ========== RELATIONS ==========

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Alias for productVariant relationship
     */
    public function variant(): BelongsTo
    {
        return $this->productVariant();
    }

    // ========== ACCESSORS ==========

    public function getFullNameAttribute(): string
    {
        if ($this->variant_name) {
            return $this->name . ' - ' . $this->variant_name;
        }
        return $this->name;
    }

    public function getQuantityPendingAttribute(): int
    {
        return $this->quantity - $this->quantity_shipped;
    }

    public function getMarginAttribute(): float
    {
        return ($this->unit_price - ($this->cost_price ?? 0)) * $this->quantity;
    }

    public function getMarginPercentageAttribute(): ?float
    {
        if (!$this->cost_price || $this->cost_price <= 0) {
            return null;
        }
        return round((($this->unit_price - $this->cost_price) / $this->unit_price) * 100, 2);
    }

    public function getIsFullyShippedAttribute(): bool
    {
        return $this->quantity_shipped >= $this->quantity;
    }

    // ========== METHODS ==========

    public static function createFromProduct(
        Order $order,
        Product $product,
        int $quantity,
        ?ProductVariant $variant = null
    ): self {
        $price = $variant?->effective_price ?? $product->sale_price;
        $taxRate = $product->tax_rate;
        $priceExclTax = $price / (1 + ($taxRate / 100));
        $taxAmount = ($price - $priceExclTax) * $quantity;
        $total = $price * $quantity;

        return self::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'name' => $product->name,
            'sku' => $variant?->sku ?? $product->sku,
            'variant_name' => $variant?->name,
            'options' => $variant?->attributeValues->pluck('value', 'attribute.name')->toArray(),
            'quantity' => $quantity,
            'unit_price' => $price,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'cost_price' => $variant?->effective_purchase_price ?? $product->cost_price ?? $product->purchase_price,
        ]);
    }
}

