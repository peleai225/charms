<?php

namespace App\Services\Storefront;

use App\Models\Category;

/**
 * Menu des catégories de l'accueil (colonne de gauche, à côté de la grande bannière).
 */
class CategoryMenu
{
    /**
     * @return array<int, array{id: int, name: string, slug: string, icon: ?string, children: array}>
     */
    public function forHome(int $limit = 12): array
    {
        return Category::active()
            ->roots()
            ->ordered()
            ->with(['children' => fn ($q) => $q->active()->ordered()])
            ->take($limit)
            ->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
                'children' => $c->children->map(fn (Category $child) => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }
}
