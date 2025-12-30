<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'barcode',
        'qr_code',
        'purchase_price',
        'sale_price',
        'compare_price',
        'stock_quantity',
        'stock_alert_threshold',
        'weight',
        'image',
        'is_active',
        'position',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'stock_alert_threshold' => 'integer',
        'weight' => 'decimal:3',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    // ========== RELATIONS ==========

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_values')
            ->withPivot('attribute_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ========== SCOPES ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // ========== ACCESSORS ==========

    public function getEffectivePriceAttribute(): float
    {
        return $this->sale_price ?? $this->product->sale_price;
    }

    public function getEffectivePurchasePriceAttribute(): float
    {
        return $this->purchase_price ?? $this->product->purchase_price;
    }

    public function getIsInStockAttribute(): bool
    {
        return $this->stock_quantity > 0 || $this->product->allow_backorder;
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return $this->product->primary_image_url;
    }

    public function getFullNameAttribute(): string
    {
        return $this->product->name . ' - ' . $this->name;
    }

    // ========== METHODS ==========

    public function generateName(): string
    {
        $values = $this->attributeValues->pluck('value')->implode(' / ');
        $this->update(['name' => $values]);
        return $values;
    }
}

