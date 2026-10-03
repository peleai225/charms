<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Promotion;

/**
 * Moteur unique des offres par lot. Prend un panier, rend des remises par ligne.
 *
 * Remplace les cinq implémentations dispersées de la règle de prix en gros :
 * Product::getBulkUnitPrice, Category::getBulkUnitPrice, Cart::recalcBulkPrices,
 * Cart::recalcCategoryBulkPrices et le calcul JavaScript de Shop/Product.vue.
 */
class BundlePricingService
{
    /**
     * Unités éligibles à une promotion, triées par prix décroissant.
     *
     * Le tri décroissant met les articles les plus chers dans le lot : la remise
     * est maximale pour le client, ce qui est l'usage du commerce.
     *
     * @param  iterable<CartItem>  $items
     * @param  array<int,int>  $consumed  cart_item_id => unités déjà prises
     * @return array<int, array{cart_item_id:int, price:float}>
     */
    protected function pool(Promotion $promotion, iterable $items, array $consumed): array
    {
        $categoryIds = $promotion->eligibleCategoryIds();
        $productIds = $promotion->products->pluck('id')->all();

        $units = [];

        foreach ($items as $item) {
            if (! $this->isEligible($item, $categoryIds, $productIds)) {
                continue;
            }

            $price = (float) $item->unit_price;

            if (! $promotion->matchesPrice($price)) {
                continue;
            }

            $available = $item->quantity - ($consumed[$item->id] ?? 0);

            for ($i = 0; $i < $available; $i++) {
                $units[] = ['cart_item_id' => $item->id, 'price' => $price];
            }
        }

        usort($units, fn (array $a, array $b) => $b['price'] <=> $a['price']);

        return $units;
    }

    /**
     * Un produit nommé explicitement, ou rattaché à une catégorie couverte.
     * Un produit sans catégorie n'est jamais capté par une offre de catégorie.
     *
     * @param  int[]  $categoryIds
     * @param  int[]  $productIds
     */
    protected function isEligible(CartItem $item, array $categoryIds, array $productIds): bool
    {
        if (in_array($item->product_id, $productIds, true)) {
            return true;
        }

        return $item->product->category_id !== null
            && in_array($item->product->category_id, $categoryIds, true);
    }
}
