<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    protected $fillable = [
        'customer_id',
        'order_id',
        'type',
        'points',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'points'        => 'integer',
        'balance_after' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getIsEarnAttribute(): bool
    {
        return $this->points > 0;
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'earn'   => 'text-green-600',
            'redeem' => 'text-red-600',
            'expire' => 'text-slate-500',
            default  => 'text-blue-600',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'earn'   => 'Points gagnés',
            'redeem' => 'Points utilisés',
            'expire' => 'Points expirés',
            'adjust' => 'Ajustement',
            default  => $this->type,
        };
    }
}
