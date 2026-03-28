<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    protected $fillable = [
        'name', 'description', 'type', 'status', 'message_template',
        'target_tags', 'target_filters', 'recipients_count', 'sent_count',
        'delivered_count', 'read_count', 'clicked_count',
        'scheduled_at', 'started_at', 'completed_at', 'created_by',
    ];

    protected $casts = [
        'target_tags' => 'array',
        'target_filters' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTargetCustomers()
    {
        $query = Customer::active();

        if ($this->target_tags && count($this->target_tags) > 0) {
            $query->whereHas('tags', fn($q) => $q->whereIn('customer_tags.id', $this->target_tags));
        }

        $filters = $this->target_filters ?? [];
        if (isset($filters['min_orders'])) $query->where('orders_count', '>=', $filters['min_orders']);
        if (isset($filters['min_spent'])) $query->where('total_spent', '>=', $filters['min_spent']);
        if (isset($filters['segment'])) $query->where('segment', $filters['segment']);

        return $query;
    }

    public function getDeliveryRateAttribute(): float
    {
        return $this->sent_count > 0 ? round(($this->delivered_count / $this->sent_count) * 100, 1) : 0;
    }

    public function getReadRateAttribute(): float
    {
        return $this->delivered_count > 0 ? round(($this->read_count / $this->delivered_count) * 100, 1) : 0;
    }
}
