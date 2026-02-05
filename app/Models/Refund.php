<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'refund_number',
        'amount',
        'reason',
        'notes',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public const REASON_CUSTOMER_REQUEST = 'customer_request';
    public const REASON_PRODUCT_DEFECTIVE = 'product_defective';
    public const REASON_WRONG_ITEM = 'wrong_item';
    public const REASON_NOT_DELIVERED = 'not_delivered';
    public const REASON_DUPLICATE = 'duplicate';
    public const REASON_OTHER = 'other';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_REJECTED = 'rejected';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($refund) {
            if (empty($refund->refund_number)) {
                $refund->refund_number = self::generateRefundNumber();
            }
        });
    }

    // ========== RELATIONS ==========

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ========== METHODS ==========

    public static function generateRefundNumber(): string
    {
        return 'RMB-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            self::REASON_CUSTOMER_REQUEST => 'Demande client',
            self::REASON_PRODUCT_DEFECTIVE => 'Produit défectueux',
            self::REASON_WRONG_ITEM => 'Mauvais article',
            self::REASON_NOT_DELIVERED => 'Non livré',
            self::REASON_DUPLICATE => 'Doublon',
            self::REASON_OTHER => 'Autre',
            default => $this->reason,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_APPROVED => 'Approuvé',
            self::STATUS_PROCESSED => 'Traité',
            self::STATUS_REJECTED => 'Rejeté',
            default => $this->status,
        };
    }
}
