<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Promotion;
use App\Support\CartPricing;
use App\Support\PricedLine;

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
     * Tarifie un panier : une passe par offre, dans l'ordre de résolution, chaque
     * unité ne pouvant être remisée qu'une fois.
     */
    public function price(Cart $cart): CartPricing
    {
        $items = $cart->items()->with(['product.category', 'variant'])->get();

        if ($items->isEmpty()) {
            return new CartPricing([]);
        }

        $discounts = [];   // cart_item_id => remise cumulée
        $promotions = [];  // cart_item_id => première offre appliquée
        $consumed = [];    // cart_item_id => unités déjà prises

        $candidates = Promotion::valid()
            ->resolutionOrder()
            ->with(['categories', 'products'])
            ->get();

        foreach ($candidates as $promotion) {
            $result = $this->applyPromotion($promotion, $this->pool($promotion, $items, $consumed));

            foreach ($result['discounts'] as $id => $share) {
                $discounts[$id] = ($discounts[$id] ?? 0) + $share;
                $promotions[$id] ??= $promotion;
            }

            foreach ($result['consumed'] as $id => $count) {
                $consumed[$id] = ($consumed[$id] ?? 0) + $count;
            }
        }

        $lines = [];

        foreach ($items as $item) {
            $lines[$item->id] = new PricedLine(
                cartItemId: $item->id,
                quantity: (int) $item->quantity,
                unitPrice: (float) $item->unit_price,
                discount: (float) ($discounts[$item->id] ?? 0),
                promotion: $promotions[$item->id] ?? null,
            );
        }

        $pricing = new CartPricing($lines);

        return $pricing->withCouponDiscount($this->couponDiscount($cart, $pricing));
    }

    /**
     * Offres à portée de main : pour chaque promotion dont le vivier n'est pas un
     * multiple de lot_qty, combien d'unités manquent et ce que le prochain lot
     * complet ferait gagner.
     *
     * @return array<int, array{promotion_id:int, promotion_name:string,
     *                          category_name:?string, items_needed:int,
     *                          current_qty:int, next_tier_qty:int,
     *                          total_saving:float, shop_url:string}>
     */
    public function nudges(Cart $cart): array
    {
        $items = $cart->items()->with(['product.category', 'variant'])->get();

        if ($items->isEmpty()) {
            return [];
        }

        $candidates = Promotion::valid()
            ->resolutionOrder()
            ->with(['categories', 'products'])
            ->get();

        $nudges = [];

        foreach ($candidates as $promotion) {
            $units = $this->pool($promotion, $items, []);
            $count = count($units);

            if ($count === 0) {
                continue;
            }

            $lotQty = (int) $promotion->lot_qty;
            $lots = intdiv($count, $lotQty);
            $remainder = $count % $lotQty;

            // Lot complet : rien à suggérer.
            if ($remainder === 0) {
                continue;
            }

            // Plafond atteint : un nudge serait mensonger.
            if ($promotion->max_lots_per_order !== null && $lots >= (int) $promotion->max_lots_per_order) {
                continue;
            }

            $needed = $lotQty - $remainder;

            // Les unités manquantes sont projetées au prix de la moins chère déjà
            // présente : hypothèse prudente, l'économie annoncée n'est jamais survendue.
            $cheapest = min(array_column($units, 'price'));
            $tail = array_slice($units, $lots * $lotQty);
            $projected = array_sum(array_column($tail, 'price')) + $needed * $cheapest;

            $category = $promotion->categories->first();

            $nudges[] = [
                'promotion_id' => $promotion->id,
                'promotion_name' => $promotion->name,
                'category_name' => $category?->name,
                'items_needed' => $needed,
                'current_qty' => $remainder,
                'next_tier_qty' => $lotQty,
                'total_saving' => max(0, $projected - (float) $promotion->lot_price),
                'shop_url' => $category ? '/boutique?category='.$category->slug : '/boutique',
            ];
        }

        return $nudges;
    }

    /**
     * Remise coupon, calculée sur la seule base éligible : les lignes en offre en
     * sont exclues sauf offre cumulable.
     */
    protected function couponDiscount(Cart $cart, CartPricing $pricing): float
    {
        if (! $cart->coupon_code || ! $cart->coupon) {
            return 0.0;
        }

        return (float) $cart->coupon->calculateDiscount($pricing->couponEligibleBase());
    }

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

    /**
     * Applique une promotion à un vivier et rend les remises par ligne de panier.
     *
     * @param  array<int, array{cart_item_id:int, price:float}>  $units  trié par prix décroissant
     * @return array{discounts: array<int,float>, consumed: array<int,int>}
     */
    protected function applyPromotion(Promotion $promotion, array $units): array
    {
        $lotQty = (int) $promotion->lot_qty;

        if ($lotQty < 1) {
            return ['discounts' => [], 'consumed' => []];
        }

        $lots = intdiv(count($units), $lotQty);

        if ($promotion->max_lots_per_order !== null) {
            $lots = min($lots, (int) $promotion->max_lots_per_order);
        }

        $discounts = [];
        $consumed = [];

        for ($lot = 0; $lot < $lots; $lot++) {
            $lotUnits = array_slice($units, $lot * $lotQty, $lotQty);
            $prices = array_column($lotUnits, 'price');

            // max(0, …) : une offre mal saisie ne renchérit jamais le panier.
            $discount = max(0, array_sum($prices) - (float) $promotion->lot_price);

            foreach ($this->distribute($discount, $prices) as $index => $share) {
                $id = $lotUnits[$index]['cart_item_id'];

                // 0.0 et non 0 : les remises sont des flottants, y compris quand
                // la répartition rend des parts nulles.
                $discounts[$id] = ($discounts[$id] ?? 0.0) + $share;
                $consumed[$id] = ($consumed[$id] ?? 0) + 1;
            }
        }

        return ['discounts' => $discounts, 'consumed' => $consumed];
    }

    /**
     * Répartit un montant au prorata de poids, en entiers, par la méthode du plus
     * grand reste. La somme des parts vaut exactement le montant : en F CFA, qui
     * n'a pas de sous-unité, un arrondi ligne par ligne ferait dériver le total
     * du lot de son prix annoncé.
     *
     * @param  float[]  $weights
     * @return int[] mêmes clés que $weights
     */
    protected function distribute(float $total, array $weights): array
    {
        $target = (int) round($total);
        $sum = array_sum($weights);

        if ($target <= 0 || $sum <= 0) {
            return array_map(fn () => 0, $weights);
        }

        $shares = [];
        $remainders = [];
        $assigned = 0;

        foreach ($weights as $index => $weight) {
            $exact = $target * $weight / $sum;
            $shares[$index] = (int) floor($exact);
            $remainders[$index] = $exact - $shares[$index];
            $assigned += $shares[$index];
        }

        // Les unités restantes vont aux plus grands restes.
        arsort($remainders);

        foreach (array_keys($remainders) as $index) {
            if ($assigned >= $target) {
                break;
            }

            $shares[$index]++;
            $assigned++;
        }

        ksort($shares);

        return $shares;
    }
}
