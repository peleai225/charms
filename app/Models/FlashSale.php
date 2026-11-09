<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Vente flash : un pourcentage de remise sur une liste de produits (variantes
 * comprises), entre starts_at et ends_at.
 *
 * Règles : une vente est « en cours » si elle est active, commencée et pas
 * terminée. Si plusieurs ventes en cours couvrent un produit, la plus forte
 * remise l'emporte (voir App\Services\FlashSalePricing).
 */
class FlashSale extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_RUNNING = 'running';

    public const STATUS_ENDED = 'ended';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'discount_percent',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'discount_percent' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ========== RELATIONS ==========

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'flash_sale_product')
            ->orderBy('flash_sale_product.id');
    }

    // ========== SCOPES ==========

    /**
     * Ventes en cours : actives, commencées, pas encore terminées.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now());
    }

    // ========== PRIX ==========

    /**
     * Prix flash d'un prix normal, arrondi au franc (le F CFA n'a pas de centimes).
     */
    public function priceFor(float $price): float
    {
        return (float) round($price * (100 - $this->discount_percent) / 100);
    }

    // ========== ACCESSORS ==========

    public function getStatusAttribute(): string
    {
        if (! $this->is_active) {
            return self::STATUS_INACTIVE;
        }

        if ($this->ends_at && $this->ends_at->lte(now())) {
            return self::STATUS_ENDED;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return self::STATUS_SCHEDULED;
        }

        return self::STATUS_RUNNING;
    }
}
