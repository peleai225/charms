<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'sku',
        'barcode',
        'qr_code',
        'purchase_price',
        'sale_price',
        'compare_price',
        'cost_price',
        'tax_rate',
        'stock_quantity',
        'stock_alert_threshold',
        'track_stock',
        'allow_backorder',
        'category_id',
        'type',
        'has_variants',
        'weight',
        'length',
        'width',
        'height',
        'meta_title',
        'meta_description',
        'status',
        'is_featured',
        'is_new',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'stock_quantity' => 'integer',
        'stock_alert_threshold' => 'integer',
        'track_stock' => 'boolean',
        'allow_backorder' => 'boolean',
        'has_variants' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'weight' => 'decimal:3',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = strtoupper(Str::random(10));
            }
        });
    }

    // ========== RELATIONS ==========

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_primary', true);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')
            ->withPivot(['supplier_sku', 'purchase_price', 'min_order_quantity', 'lead_time_days', 'is_primary'])
            ->withTimestamps();
    }

    // ========== SCOPES ==========

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'sale_price');
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('stock_quantity', '>', 0)
                ->orWhere('allow_backorder', true);
        });
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'stock_alert_threshold')
            ->where('stock_quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0)
            ->where('allow_backorder', false);
    }

    // ========== ACCESSORS ==========

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $image = $this->images->where('is_primary', true)->first();
        return $image ? asset('storage/' . $image->path) : null;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->compare_price && $this->compare_price > $this->sale_price;
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->is_on_sale) {
            return null;
        }
        return round((($this->compare_price - $this->sale_price) / $this->compare_price) * 100);
    }

    public function getPriceExclTaxAttribute(): float
    {
        return $this->sale_price / (1 + ($this->tax_rate / 100));
    }

    public function getTaxAmountAttribute(): float
    {
        return $this->sale_price - $this->price_excl_tax;
    }

    public function getMarginAttribute(): float
    {
        return $this->sale_price - ($this->cost_price ?? $this->purchase_price);
    }

    public function getMarginPercentageAttribute(): ?float
    {
        $cost = $this->cost_price ?? $this->purchase_price;
        if (!$cost || $cost <= 0) {
            return null;
        }
        return round((($this->sale_price - $cost) / $this->sale_price) * 100, 2);
    }

    public function getAverageRatingAttribute(): ?float
    {
        return $this->reviews()->where('status', 'approved')->avg('rating');
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->where('status', 'approved')->count();
    }

    public function getIsInStockAttribute(): bool
    {
        if ($this->has_variants) {
            return $this->variants()->where('stock_quantity', '>', 0)->exists();
        }
        return $this->stock_quantity > 0 || $this->allow_backorder;
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->has_variants) {
            return $this->variants()->sum('stock_quantity');
        }
        return $this->stock_quantity;
    }

    public function getStockValueAttribute(): float
    {
        return $this->total_stock * ($this->cost_price ?? $this->purchase_price);
    }

    // ========== METHODS ==========

    public function decrementStock(int $quantity, ?int $variantId = null): void
    {
        if ($variantId) {
            $this->variants()->where('id', $variantId)->decrement('stock_quantity', $quantity);
        } else {
            $this->decrement('stock_quantity', $quantity);
        }
    }

    public function incrementStock(int $quantity, ?int $variantId = null): void
    {
        if ($variantId) {
            $this->variants()->where('id', $variantId)->increment('stock_quantity', $quantity);
        } else {
            $this->increment('stock_quantity', $quantity);
        }
    }

    public function generateBarcode(): string
    {
        $barcode = '200' . str_pad($this->id, 9, '0', STR_PAD_LEFT);
        $this->update(['barcode' => $barcode]);
        return $barcode;
    }
}

