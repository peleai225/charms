<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\LoyaltyTransaction;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'gender',
        'company_name',
        'vat_number',
        'siret',
        'status',
        'type',
        'customer_group',
        'newsletter',
        'accepts_marketing',
        'referral_source',
        'notes',
        'loyalty_points',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'newsletter' => 'boolean',
        'accepts_marketing' => 'boolean',
        'orders_count' => 'integer',
        'total_spent' => 'decimal:2',
        'last_order_at' => 'datetime',
        'loyalty_points' => 'integer',
    ];

    // ========== RELATIONS ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function defaultShippingAddress(): HasMany
    {
        return $this->hasMany(CustomerAddress::class)
            ->where('type', 'shipping')
            ->where('is_default', true);
    }

    public function defaultBillingAddress(): HasMany
    {
        return $this->hasMany(CustomerAddress::class)
            ->where('type', 'billing')
            ->where('is_default', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class)->latest();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(CustomerTag::class, 'customer_customer_tag', 'customer_id', 'customer_tag_id');
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class)->latest();
    }

    // ========== SCOPES ==========

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVip($query)
    {
        return $query->whereHas('tags', fn($q) => $q->where('slug', 'vip'))
            ->orWhere('customer_group', 'vip')
            ->orWhere(fn($q) => $q->where('orders_count', '>=', 10)->orWhere('total_spent', '>=', 500000));
    }

    public function scopeLoyal($query)
    {
        return $query->where('orders_count', '>=', 3)->where('orders_count', '<', 10);
    }

    public function scopeNew($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    public function scopeInactive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('last_order_at')
              ->orWhere('last_order_at', '<', now()->subDays(90));
        })->where('orders_count', '>', 0);
    }

    public function scopeWithOrders($query)
    {
        return $query->where('orders_count', '>', 0);
    }

    public function scopeNewsletter($query)
    {
        return $query->where('newsletter', true);
    }

    // ========== ACCESSORS ==========

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->company_name) {
            return $this->company_name . ' (' . $this->full_name . ')';
        }
        return $this->full_name;
    }

    public function getAverageOrderValueAttribute(): float
    {
        if ($this->orders_count === 0) {
            return 0;
        }
        return $this->total_spent / $this->orders_count;
    }

    // ========== METHODS ==========

    public function updateStats(): void
    {
        $stats = $this->orders()
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->selectRaw('COUNT(*) as count, SUM(total) as total, MAX(created_at) as last_order')
            ->first();

        $this->update([
            'orders_count' => $stats->count ?? 0,
            'total_spent' => $stats->total ?? 0,
            'last_order_at' => $stats->last_order,
        ]);
    }

    public function addLoyaltyPoints(int $points): void
    {
        $this->increment('loyalty_points', $points);
    }

    public function useLoyaltyPoints(int $points): bool
    {
        if ($this->loyalty_points < $points) {
            return false;
        }
        $this->decrement('loyalty_points', $points);
        return true;
    }
}

