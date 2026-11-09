<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * Prix des ventes flash. Seule source de la règle, pour le panier, la fiche
 * produit et l'accueil.
 *
 * - Prix flash = prix normal × (1 − remise), arrondi au franc. Les variantes
 *   prennent la même remise, appliquée à leur propre prix.
 * - Plusieurs ventes en cours sur un produit : la plus forte remise l'emporte ;
 *   à remise égale, celle qui finit le plus tard (le prix tient jusque-là).
 * - Une ligne de panier au prix flash garde la vente d'origine (flash_sale_id).
 *   syncCart() la remet au prix normal dès que plus aucune vente ne la couvre :
 *   un prix flash n'est jamais facturé après la fin de la vente.
 */
class FlashSalePricing
{
    /**
     * Vente flash qui fixe le prix de chaque produit, pour ceux qui en ont une.
     *
     * @param  int[]  $productIds
     * @return array<int, FlashSale> indexé par product_id
     */
    public function bestSales(array $productIds): array
    {
        $productIds = array_values(array_unique(array_map('intval', $productIds)));

        if ($productIds === []) {
            return [];
        }

        $sales = FlashSale::current()
            ->whereHas('products', fn ($q) => $q->whereIn('products.id', $productIds))
            ->with(['products' => fn ($q) => $q->whereIn('products.id', $productIds)->select('products.id')])
            ->orderByDesc('discount_percent')
            ->orderByDesc('ends_at')
            ->orderBy('id')
            ->get();

        $best = [];

        foreach ($sales as $sale) {
            foreach ($sale->products as $product) {
                $best[$product->id] ??= $sale;
            }
        }

        return $best;
    }

    /**
     * Cartes produit des listes (boutique, catégorie, accueil, favoris…) : un produit couvert par
     * une vente en cours affiche le prix que le panier facturera. price = prix flash,
     * compare_price = prix normal (le badge « -X % » en découle), pas d'étiquette d'offre par lot
     * (les lignes flash n'en profitent pas).
     *
     * @param  iterable<array>  $cards  cartes avec au moins id et price (prix normal)
     * @return array<int, array>
     */
    public function applyToCards(iterable $cards): array
    {
        $cards = collect($cards)->map(fn ($card) => (array) $card)->values();
        $best = $this->bestSales($cards->pluck('id')->all());

        return $cards->map(function (array $card) use ($best) {
            $sale = $best[$card['id']] ?? null;
            if (! $sale) {
                return $card;
            }

            $regular = (float) $card['price'];

            return array_merge($card, [
                'price' => $sale->priceFor($regular),
                'compare_price' => $regular,
                'promotion_label' => null,
            ]);
        })->all();
    }

    public function saleFor(Product $product): ?FlashSale
    {
        return $this->bestSales([$product->id])[$product->id] ?? null;
    }

    /**
     * Prix normal (hors vente flash) : celui de la variante, sinon du produit.
     */
    public function regularPrice(Product $product, ?ProductVariant $variant = null): float
    {
        return (float) ($variant?->sale_price ?? $product->sale_price);
    }

    /**
     * Prix à facturer maintenant, et la vente qui le fixe s'il y en a une.
     *
     * @return array{0: float, 1: ?FlashSale}
     */
    public function unitPrice(Product $product, ?ProductVariant $variant = null): array
    {
        $regular = $this->regularPrice($product, $variant);
        $sale = $this->saleFor($product);

        return [$sale ? $sale->priceFor($regular) : $regular, $sale];
    }

    /**
     * Aligne les lignes du panier sur les ventes en cours :
     * - un produit en vente prend le prix flash (y compris une ligne ajoutée
     *   avant le début de la vente) ;
     * - une ligne au prix flash dont la vente est finie, désactivée ou supprimée
     *   revient au prix normal ;
     * - les autres lignes gardent le prix figé à l'ajout.
     *
     * Les lignes déjà chargées sur le panier sont mises à jour en place, pour que
     * l'appelant affiche ou enregistre le bon prix sans recharger.
     */
    public function syncCart(Cart $cart): void
    {
        $items = $cart->relationLoaded('items') ? $cart->items : $cart->items()->get();

        if ($items->isEmpty()) {
            return;
        }

        $items->loadMissing(['product', 'variant']);
        $sales = $this->bestSales($items->pluck('product_id')->all());

        foreach ($items as $item) {
            if (! $item->product) {
                continue;
            }

            $sale = $sales[$item->product_id] ?? null;

            if ($sale === null && $item->flash_sale_id === null) {
                continue;
            }

            $regular = $this->regularPrice($item->product, $item->variant);
            $price = $sale ? $sale->priceFor($regular) : $regular;

            if ((float) $item->unit_price !== $price || $item->flash_sale_id !== $sale?->id) {
                $item->forceFill(['unit_price' => $price, 'flash_sale_id' => $sale?->id])->save();
            }
        }
    }
}
