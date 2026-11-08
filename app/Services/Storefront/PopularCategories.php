<?php

namespace App\Services\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * « Nos univers populaires » : les catégories mises en avant sur l'accueil.
 *
 * Choix de l'admin d'abord : les catégories actives cochées « Mise en avant » (principales ou
 * sous-catégories), dans l'ordre de l'admin. Si aucune n'a de produit, les catégories principales
 * qui en ont. Une catégorie compte les produits en ligne de ses sous-catégories, comme sa page.
 */
class PopularCategories
{
    /**
     * @return array<int, array{id: int, name: string, slug: string, image: ?string, icon: ?string, products_count: int}>
     */
    public function forHome(int $limit = 12): array
    {
        $categories = Category::query()
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'slug', 'image', 'icon', 'order', 'is_active', 'is_featured']);

        $counts = $this->productCounts($categories);
        $visible = $categories->filter(fn (Category $c) => $c->is_active && $counts[$c->id] > 0);

        $picked = $visible->where('is_featured', true);
        if ($picked->isEmpty()) {
            $picked = $visible->whereNull('parent_id');
        }

        return $picked
            ->take($limit)
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'image' => $c->image,
                'icon' => $c->icon,
                'products_count' => $counts[$c->id],
            ])
            ->values()
            ->all();
    }

    /**
     * Produits en ligne de chaque catégorie et de toutes ses sous-catégories (comme sa page).
     *
     * @return array<int, int> id de catégorie => nombre de produits
     */
    private function productCounts(Collection $categories): array
    {
        $direct = Product::active()
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->selectRaw('category_id, COUNT(*) as total')
            ->pluck('total', 'category_id');

        $childrenOf = $categories->groupBy('parent_id');

        $count = function (int $id, array $visited) use (&$count, $direct, $childrenOf): int {
            $visited[$id] = true;
            $total = (int) ($direct[$id] ?? 0);
            foreach ($childrenOf->get($id, collect()) as $child) {
                if (! isset($visited[$child->id])) { // garde-fou : arbre mal formé (boucle)
                    $total += $count($child->id, $visited);
                }
            }

            return $total;
        };

        return $categories->mapWithKeys(fn (Category $c) => [$c->id => $count($c->id, [])])->all();
    }
}
