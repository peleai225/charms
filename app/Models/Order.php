<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total',
        'coupon_code',
        'currency',
        'currency_rate',
        'billing_first_name',
        'billing_last_name',
        'billing_company',
        'billing_address',
        'billing_address_2',
        'billing_city',
        'billing_postal_code',
        'billing_country',
        'billing_phone',
        'billing_email',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_company',
        'shipping_address',
        'shipping_address_2',
        'shipping_city',
        'shipping_postal_code',
        'shipping_country',
        'shipping_phone',
        'shipping_email',
        'shipping_method',
        'shipping_carrier',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'paid_at',
        'total_weight',
        'notes',
        'customer_notes',
        'admin_notes',
        'source',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'currency_rate' => 'decimal:6',
        'total_weight' => 'decimal:3',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_PARTIALLY_PAID = 'partially_paid';
    public const PAYMENT_REFUNDED = 'refunded';
    public const PAYMENT_FAILED = 'failed';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    // ========== RELATIONS ==========

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function orderSuppliers(): HasMany
    {
        return $this->hasMany(OrderSupplier::class);
    }

    public function suppliers()
    {
        return $this->hasManyThrough(
            Supplier::class,
            OrderSupplier::class,
            'order_id',
            'id',
            'id',
            'supplier_id'
        );
    }

    // ========== SCOPES ==========

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_PROCESSING]);
    }

    public function scopeShipped($query)
    {
        return $query->where('status', self::STATUS_SHIPPED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PENDING);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ========== ACCESSORS ==========

    public function getBillingFullNameAttribute(): string
    {
        return $this->billing_first_name . ' ' . $this->billing_last_name;
    }

    public function getShippingFullNameAttribute(): string
    {
        return $this->shipping_first_name . ' ' . $this->shipping_last_name;
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function getIsCancellableAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function getIsRefundableAttribute(): bool
    {
        $paid = in_array($this->payment_status, [self::PAYMENT_PAID, 'cod']);
        return $paid && !in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_REFUNDED]);
    }

    public function getPaidAmountAttribute(): float
    {
        return $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function getMarginAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return ($item->unit_price - ($item->cost_price ?? 0)) * $item->quantity;
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CONFIRMED => 'Confirmée',
            self::STATUS_PROCESSING => 'En préparation',
            self::STATUS_SHIPPED => 'Expédiée',
            self::STATUS_DELIVERED => 'Livrée',
            self::STATUS_CANCELLED => 'Annulée',
            self::STATUS_REFUNDED => 'Remboursée',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED, self::STATUS_PROCESSING => 'info',
            self::STATUS_SHIPPED => 'primary',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED, self::STATUS_REFUNDED => 'danger',
            default => 'secondary',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cinetpay' => 'CinetPay (Orange Money, MTN MoMo, etc.)',
            'lygos' => 'Lygos Pay',
            'cod' => 'Paiement à la livraison',
            'bank_transfer' => 'Virement bancaire',
            'cash' => 'Espèces',
            default => ucfirst(str_replace('_', ' ', $this->payment_method ?? 'Non spécifié')),
        };
    }

    // ========== METHODS ==========

    public static function generateOrderNumber(): string
    {
        $prefix = 'CMD';
        $date = now()->format('ymd');
        $random = strtoupper(Str::random(4));
        return $prefix . '-' . $date . '-' . $random;
    }

    public function updateStatus(string $status): void
    {
        $this->update(['status' => $status]);

        // Actions automatiques selon le statut
        if ($status === self::STATUS_SHIPPED) {
            $this->update(['shipped_at' => now()]);
        }

        if ($status === self::STATUS_DELIVERED) {
            $this->update(['delivered_at' => now()]);
        }
    }

    public function cancel(): void
    {
        if (!$this->is_cancellable) {
            throw new \Exception('Cette commande ne peut pas être annulée.');
        }

        $this->updateStatus(self::STATUS_CANCELLED);

        // Remettre le stock
        foreach ($this->items as $item) {
            if ($item->product) {
                StockMovement::createMovement(
                    $item->product,
                    StockMovement::TYPE_RETURN_IN,
                    $item->quantity,
                    $item->productVariant,
                    $item->unit_price,
                    $this,
                    'Annulation commande ' . $this->order_number
                );
            }
        }
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items->sum('total');
        $taxAmount = $this->items->sum('tax_amount');

        $this->update([
            'subtotal' => $subtotal - $taxAmount,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $this->shipping_amount - $this->discount_amount,
        ]);
    }
}

