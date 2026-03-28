<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class CustomerTag extends Model
{
    protected $fillable = [
        'name', 'slug', 'color', 'icon', 'description',
        'is_auto', 'auto_rules', 'sort_order',
    ];

    protected $casts = [
        'is_auto' => 'boolean',
        'auto_rules' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)->withPivot('assigned_at');
    }

    /**
     * Auto-classify a customer based on rules
     */
    public static function autoClassify(Customer $customer): void
    {
        $autoTags = static::where('is_auto', true)->get();

        foreach ($autoTags as $tag) {
            $rules = $tag->auto_rules ?? [];
            if (empty($rules)) continue;

            $matches = true;

            if (isset($rules['min_orders']) && $customer->orders_count < $rules['min_orders']) {
                $matches = false;
            }
            if (isset($rules['min_spent']) && $customer->total_spent < $rules['min_spent']) {
                $matches = false;
            }
            if (isset($rules['max_orders']) && $customer->orders_count > $rules['max_orders']) {
                $matches = false;
            }
            if (isset($rules['inactive_days'])) {
                if ($customer->last_order_at) {
                    if ($customer->last_order_at->diffInDays(now()) < $rules['inactive_days']) {
                        $matches = false;
                    }
                } else {
                    // No orders yet - only match if no min_orders/min_spent rule required
                    if (isset($rules['min_orders']) || isset($rules['min_spent'])) {
                        $matches = false;
                    }
                }
            }

            if ($matches) {
                $customer->tags()->syncWithoutDetaching([$tag->id]);
            } else {
                $customer->tags()->detach($tag->id);
            }
        }
    }
}
