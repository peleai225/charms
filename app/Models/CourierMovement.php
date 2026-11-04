<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LogicException;

/**
 * Point ou versement d'un livreur. Une fois enregistré, il ne se modifie plus :
 * une erreur se corrige par un nouveau mouvement, visible dans l'historique.
 */
class CourierMovement extends Model
{
    public const TYPE_POINT = 'point';

    public const TYPE_PAYMENT = 'payment';

    protected $fillable = [
        'courier_id', 'type', 'amount_collected', 'fees', 'amount_received', 'balance_change', 'note', 'user_id',
    ];

    protected $casts = [
        'amount_collected' => 'decimal:2',
        'fees' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'balance_change' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Un mouvement validé ne se modifie pas.');
        });
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'courier_movement_orders')
            ->withPivot('outcome', 'issue', 'amount_collected', 'fee');
    }
}
