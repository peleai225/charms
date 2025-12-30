<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_limit_per_user',
        'usage_count',
        'starts_at',
        'expires_at',
        'is_active',
        'first_order_only',
        'applicable_categories',
        'applicable_products',
        'excluded_products',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'starts_at' => 'date',
        'expires_at' => 'date',
        'is_active' => 'boolean',
        'first_order_only' => 'boolean',
        'applicable_categories' => 'array',
        'applicable_products' => 'array',
        'excluded_products' => 'array',
    ];

    /**
     * Usages du coupon
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Scope actif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope valide (actif et dans les dates)
     */
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereColumn('usage_count', '<', 'usage_limit');
            });
    }

    /**
     * Vérifier si le coupon est valide
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Vérifier si un client peut utiliser ce coupon
     */
    public function canBeUsedBy(?Customer $customer, float $orderAmount): array
    {
        if (!$this->isValid()) {
            return ['valid' => false, 'message' => 'Ce code promo n\'est plus valide.'];
        }

        if ($this->min_order_amount && $orderAmount < $this->min_order_amount) {
            return ['valid' => false, 'message' => 'Montant minimum de commande : ' . format_price($this->min_order_amount)];
        }

        if ($customer && $this->usage_limit_per_user) {
            $userUsage = $this->usages()->where('customer_id', $customer->id)->count();
            if ($userUsage >= $this->usage_limit_per_user) {
                return ['valid' => false, 'message' => 'Vous avez déjà utilisé ce code promo.'];
            }
        }

        if ($this->first_order_only && $customer) {
            if ($customer->orders()->where('status', '!=', 'cancelled')->exists()) {
                return ['valid' => false, 'message' => 'Ce code est réservé aux nouvelles commandes.'];
            }
        }

        return ['valid' => true, 'message' => 'Code promo valide !'];
    }

    /**
     * Calculer la réduction
     */
    public function calculateDiscount(float $amount): float
    {
        $discount = 0;

        switch ($this->type) {
            case 'percentage':
                $discount = $amount * ($this->value / 100);
                break;
            case 'fixed':
                $discount = $this->value;
                break;
            case 'free_shipping':
                return 0; // Géré séparément
        }

        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        return min($discount, $amount);
    }

    /**
     * Incrémenter l'usage
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Libellé du type
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'percentage' => $this->value . '%',
            'fixed' => format_price($this->value),
            'free_shipping' => 'Livraison gratuite',
            default => $this->type,
        };
    }

    /**
     * Statut du coupon
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return 'exhausted';
        }

        return 'active';
    }
}
