<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMedia extends Model
{
    protected $fillable = [
        'product_id', 'type', 'file_path', 'external_url',
        'title', 'duration_seconds', 'language', 'sort_order',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        if ($this->external_url) return $this->external_url;
        return $this->file_path ? asset('storage/' . $this->file_path) : '';
    }
}
