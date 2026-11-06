<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Livreur extérieur (indépendant ou société) à qui l'on confie des commandes.
 */
class Courier extends Model
{
    public const TYPE_INDEPENDENT = 'independent';

    public const TYPE_COMPANY = 'company';

    protected $fillable = ['name', 'phone', 'type', 'notes', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function ordersOnRoute(): HasMany
    {
        return $this->orders()->where('status', Order::STATUS_DELIVERY_IN_PROGRESS);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CourierMovement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Positif : le livreur doit à la boutique. Négatif : la boutique lui doit.
     */
    public function balance(): float
    {
        return (float) $this->movements()->sum('balance_change');
    }
}
