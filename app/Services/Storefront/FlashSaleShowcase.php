<?php

namespace App\Services\Storefront;

use App\Models\FlashSale;
use App\Models\Product;
use App\Services\FlashSalePricing;
use Illuminate\Support\Collection;

/**
 * « Ventes Flash » de la boutique : les ventes en cours, leurs produits au prix
 * flash et leur heure de fin.
 *
 * Un produit couvert par plusieurs ventes est montré dans celle qui fixe son
 * prix (la plus forte remise, cf. FlashSalePricing) : le compte à rebours
 * affiché à côté est donc bien l'heure où ce prix prend fin. Une vente qui ne
 * fixe le prix d'aucun produit actif n'est pas montrée.
 */
class FlashSaleShowcase
{
    /** Produits montrés dans le bloc de l'accueil. */
    public const HOME_LIMIT = 12;

    /** Garde-fou de la page /ventes-flash. */
    public const PAGE_LIMIT = 60;

    public function __construct(private FlashSalePricing $pricing) {}

    /**
     * Bloc de l'accueil : la vente en cours qui se termine le plus tôt, ou null.
     *
     * Chaque produit reprend la carte de HomeController ($formatProduct), avec
     * price = prix flash et compare_price = prix normal (le badge « -X % » de
     * ProductCard en découle).
     *
     * @param  callable(Product): array  $formatProduct  formateur de carte produit de HomeController
     * @return array{id: int, name: string, discount_percent: int, ends_at: string, products: array}|null
     */
    public function current(callable $formatProduct): ?array
    {
        foreach ($this->runningSales() as $sale) {
            $products = $this->productsPricedBy($sale, self::HOME_LIMIT);

            if ($products->isNotEmpty()) {
                return $this->present($sale, $products, $formatProduct);
            }
        }

        return null;
    }

    /**
     * Toutes les ventes en cours, de la plus proche de sa fin à la plus lointaine.
     *
     * @param  callable(Product): array  $formatProduct
     * @return array<int, array{id: int, name: string, discount_percent: int, ends_at: string, products: array}>
     */
    public function all(callable $formatProduct): array
    {
        $sales = [];

        foreach ($this->runningSales() as $sale) {
            $products = $this->productsPricedBy($sale, self::PAGE_LIMIT);

            if ($products->isNotEmpty()) {
                $sales[] = $this->present($sale, $products, $formatProduct);
            }
        }

        return $sales;
    }

    /**
     * @return Collection<int, FlashSale>
     */
    private function runningSales(): Collection
    {
        return FlashSale::current()->orderBy('ends_at')->orderBy('id')->get();
    }

    /**
     * Produits actifs dont cette vente fixe le prix, dans l'ordre de saisie.
     *
     * @return Collection<int, Product>
     */
    private function productsPricedBy(FlashSale $sale, int $limit): Collection
    {
        $ids = $sale->products()->active()->pluck('products.id')->all();
        $best = $this->pricing->bestSales($ids);

        $ids = array_slice(
            array_values(array_filter($ids, fn (int $id) => ($best[$id] ?? null)?->id === $sale->id)),
            0,
            $limit
        );

        if ($ids === []) {
            return collect();
        }

        $products = Product::whereIn('id', $ids)->with(['images', 'category', 'variants'])->get()->keyBy('id');

        return collect($ids)->map(fn (int $id) => $products->get($id))->filter()->values();
    }

    private function present(FlashSale $sale, Collection $products, callable $formatProduct): array
    {
        return [
            'id' => $sale->id,
            'name' => $sale->name,
            'discount_percent' => $sale->discount_percent,
            'ends_at' => $sale->ends_at->toIso8601String(),
            'products' => $products->map(fn (Product $product) => array_merge($formatProduct($product), [
                'price' => $sale->priceFor((float) $product->sale_price),
                'compare_price' => (float) $product->sale_price,
            ]))->values()->all(),
        ];
    }
}
