<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'path',
        'alt',
        'is_primary',
        'position',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'position' => 'integer',
    ];

    // ========== RELATIONS ==========

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // ========== ACCESSORS ==========

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        // Supposant que les thumbnails sont stockés avec le préfixe 'thumb_'
        $thumbPath = dirname($this->path) . '/thumb_' . basename($this->path);
        if (Storage::disk('public')->exists($thumbPath)) {
            return asset('storage/' . $thumbPath);
        }
        return $this->url;
    }

    // ========== METHODS ==========

    public function setAsPrimary(): void
    {
        // Retirer le statut primary des autres images
        static::where('product_id', $this->product_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }

    public function deleteFile(): void
    {
        Storage::disk('public')->delete($this->path);
        
        // Supprimer aussi le thumbnail
        $thumbPath = dirname($this->path) . '/thumb_' . basename($this->path);
        Storage::disk('public')->delete($thumbPath);
    }
}

