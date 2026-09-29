<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'parent_id',
        'order',
        'is_active',
        'is_featured',
        'bulk_pricing_rules',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'order' => 'integer',
        'bulk_pricing_rules' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // ========== RELATIONS ==========

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ========== SCOPES ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // ========== BULK PRICING ==========

    /**
     * Retourne le prix unitaire en gros pour une quantité et un prix de base donnés.
     * Si aucun palier ne correspond, retourne le prix de base inchangé.
     */
    public function getBulkUnitPrice(int $quantity, float $basePrice): float
    {
        $rules = $this->bulk_pricing_rules;

        if (empty($rules) || !is_array($rules)) {
            return $basePrice;
        }

        // Trier par min_qty décroissant pour trouver le palier le plus élevé applicable
        $sorted = collect($rules)->sortByDesc('min_qty');

        foreach ($sorted as $rule) {
            if ($quantity >= ($rule['min_qty'] ?? PHP_INT_MAX)) {
                return (float) $rule['unit_price'];
            }
        }

        return $basePrice;
    }

    // ========== HELPERS ==========

    public function getFullPathAttribute(): string
    {
        $path    = collect([$this->name]);
        $parent  = $this->parent;
        $visited = [$this->id];

        while ($parent && !in_array($parent->id, $visited)) {
            $visited[] = $parent->id;
            $path->prepend($parent->name);
            $parent = $parent->parent;
        }

        return $path->implode(' > ');
    }

    public function getAllChildrenIds(): array
    {
        $ids = [$this->id];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }

        return $ids;
    }
}

