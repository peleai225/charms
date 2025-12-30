<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'method',
        'gateway',
        'gateway_transaction_id',
        'amount',
        'currency',
        'status',
        'refunded_amount',
        'card_brand',
        'card_last4',
        'gateway_response',
        'notes',
        'processed_by',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public const METHOD_CARD = 'card';
    public const METHOD_PAYPAL = 'paypal';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_CHECK = 'check';
    public const METHOD_CASH = 'cash';
    public const METHOD_OTHER = 'other';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->transaction_id)) {
                $payment->transaction_id = self::generateTransactionId();
            }
        });
    }

    // ========== RELATIONS ==========

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ========== SCOPES ==========

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('method', $method);
    }

    // ========== ACCESSORS ==========

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            self::METHOD_CARD => 'Carte bancaire',
            self::METHOD_PAYPAL => 'PayPal',
            self::METHOD_BANK_TRANSFER => 'Virement bancaire',
            self::METHOD_CHECK => 'Chèque',
            self::METHOD_CASH => 'Espèces',
            self::METHOD_OTHER => 'Autre',
            default => $this->method,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_PROCESSING => 'En cours',
            self::STATUS_COMPLETED => 'Complété',
            self::STATUS_FAILED => 'Échoué',
            self::STATUS_CANCELLED => 'Annulé',
            self::STATUS_REFUNDED => 'Remboursé',
            self::STATUS_PARTIALLY_REFUNDED => 'Partiellement remboursé',
            default => $this->status,
        };
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getIsRefundableAttribute(): bool
    {
        return $this->is_completed && $this->refunded_amount < $this->amount;
    }

    public function getRemainingRefundableAttribute(): float
    {
        return max(0, $this->amount - $this->refunded_amount);
    }

    // ========== METHODS ==========

    public static function generateTransactionId(): string
    {
        return 'TXN-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);

        // Mettre à jour le statut de paiement de la commande
        $this->order->update(['payment_status' => Order::PAYMENT_PAID]);
    }

    public function refund(float $amount): void
    {
        if ($amount > $this->remaining_refundable) {
            throw new \Exception('Le montant du remboursement dépasse le montant disponible.');
        }

        $this->increment('refunded_amount', $amount);

        if ($this->refunded_amount >= $this->amount) {
            $this->update(['status' => self::STATUS_REFUNDED]);
        } else {
            $this->update(['status' => self::STATUS_PARTIALLY_REFUNDED]);
        }
    }
}

