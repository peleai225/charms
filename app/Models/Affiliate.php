<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'commission_rate',
        'status',
        'payment_method',
        'total_earned',
        'total_paid',
        'notes',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getPendingBalanceAttribute(): float
    {
        return (float) ($this->total_earned - $this->total_paid);
    }

    public function getEffectiveRateAttribute(): float
    {
        return (float) ($this->commission_rate ?? Setting::get('affiliate_commission_rate', 5));
    }

    public static function generateCode(string $name = ''): string
    {
        $base = $name
            ? Str::upper(Str::substr(Str::slug($name, ''), 0, 6))
            : Str::upper(Str::random(6));

        $code = $base . rand(10, 99);

        while (static::where('code', $code)->exists()) {
            $code = $base . rand(10, 99);
        }

        return $code;
    }
}
