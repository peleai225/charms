<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'customer_id',
        'endpoint',
        'endpoint_hash',
        'public_key',
        'auth_token',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $sub) {
            $sub->endpoint_hash = hash('sha256', $sub->endpoint);
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public static function findByEndpoint(string $endpoint): ?self
    {
        return static::where('endpoint_hash', hash('sha256', $endpoint))->first();
    }
}
