<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'lot_qty',
        'lot_price',
        'price_min',
        'price_max',
        'max_lots_per_order',
        'include_descendants',
        'stackable_with_coupons',
        'is_active',
        'starts_at',
        'expires_at',
        'priority',
    ];

    protected $casts = [
        'lot_qty' => 'integer',
        'lot_price' => 'decimal:2',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
        'max_lots_per_order' => 'integer',
        'include_descendants' => 'boolean',
        'stackable_with_coupons' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'priority' => 'integer',
    ];

    // ========== RELATIONS ==========

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'promotion_category');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_product');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ========== SCOPES ==========

    /**
     * Offres actives et dans leur fenêtre de validité.
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Ordre de résolution du moteur. Doit être total et reproductible : sans quoi
     * le prix affiché dépendrait de l'ordre de lecture en base. À priorité égale,
     * le prix de lot le plus bas passe d'abord — l'offre la plus avantageuse pour
     * le client consomme les unités.
     */
    public function scopeResolutionOrder($query)
    {
        return $query->orderByDesc('priority')->orderBy('lot_price')->orderBy('id');
    }

    // ========== ÉLIGIBILITÉ ==========

    /**
     * Le prix tombe-t-il dans la fourchette ? Une borne nulle ne contraint pas.
     */
    public function matchesPrice(float $price): bool
    {
        if ($this->price_min !== null && $price < (float) $this->price_min) {
            return false;
        }

        if ($this->price_max !== null && $price > (float) $this->price_max) {
            return false;
        }

        return true;
    }

    /**
     * IDs des catégories couvertes, descendants compris si include_descendants.
     *
     * @return int[]
     */
    public function eligibleCategoryIds(): array
    {
        $ids = [];

        foreach ($this->categories as $category) {
            $ids = array_merge(
                $ids,
                $this->include_descendants ? $category->getAllChildrenIds() : [$category->id]
            );
        }

        return array_values(array_unique($ids));
    }

    // ========== ACCESSORS ==========

    public function getStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }

        return 'active';
    }
}
