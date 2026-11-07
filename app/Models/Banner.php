<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'subtitle',
        'description',
        'image',
        'image_mobile',
        'link',
        'button_text',
        'background_color',
        'text_color',
        'position',
        'type',
        'order',
        'is_active',
        'starts_at',
        'ends_at',
        'show_countdown',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'show_countdown' => 'boolean',
    ];

    /**
     * Emplacements réellement affichés sur le site : les seuls proposés à la création.
     */
    const DISPLAYED_POSITIONS = [
        'announcement_bar',
        'popup_center',
        'home_hero',
        'home_side',
        'home_middle',
        'home_bottom',
    ];

    public static function selectablePositions(?string $current = null): array
    {
        return array_filter(
            self::POSITIONS,
            fn ($key) => in_array($key, self::DISPLAYED_POSITIONS, true) || $key === $current,
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Types de bannières
     */
    const TYPES = [
        'announcement' => 'Barre d\'annonce (haut du site)',
        'hero' => 'Bannière principale (Hero)',
        'promo' => 'Bannière promotionnelle',
        'category' => 'Bannière catégorie',
        'sidebar' => 'Bannière sidebar',
        'popup' => 'Popup',
    ];

    /**
     * Positions possibles
     */
    const POSITIONS = [
        'announcement_bar' => 'Barre d\'annonce (tout en haut)',
        'popup_center' => 'Popup (centre de l\'écran)',
        'home_hero' => 'Accueil - Slider principal',
        'home_side' => 'Accueil - À côté du slider (petites cartes)',
        'home_middle' => 'Accueil - Milieu de page',
        'home_bottom' => 'Accueil - Bas de page',
        'category_top' => 'Catégorie - Haut',
        'product_sidebar' => 'Produit - Sidebar',
        'cart_bottom' => 'Panier - Bas',
        'checkout_top' => 'Checkout - Haut',
    ];

    /**
     * Scope pour bannières actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Scope par position
     */
    public function scopePosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope par type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Obtenir les bannières pour une position
     */
    public static function getForPosition(string $position)
    {
        return static::active()
            ->position($position)
            ->orderBy('order')
            ->get();
    }

    /**
     * Obtenir l'URL de l'image
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }
}
