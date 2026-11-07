<?php

namespace App\Services\Storefront;

use App\Models\Category;

/**
 * « Nos univers populaires » : les catégories mises en avant sur l'accueil.
 */
class PopularCategories
{
    /**
     * @return array<int, array{id: int, name: string, slug: string, image: ?string, icon: ?string, products_count: int}>
     */
    public function forHome(int $limit = 12): array
    {
        return Category::active()
            ->featured()
            ->roots()
            ->ordered()
            ->take($limit)
            ->withCount(['products' => fn ($q) => $q->active()])
            ->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'image' => $c->image,
                'icon' => $c->icon,
                'products_count' => (int) $c->products_count,
            ])
            ->values()
            ->all();
    }
}
