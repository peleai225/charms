<?php

namespace App\Services\Storefront;

/**
 * « Ventes Flash » de l'accueil : la vente en cours, ses produits et son heure de fin.
 * (À construire : réglage dans l'admin, prix appliqué au panier.)
 */
class FlashSaleShowcase
{
    /**
     * @param  callable  $formatProduct  formateur de carte produit de HomeController
     * @return array{id: int, name: string, ends_at: string, products: array}|null
     */
    public function current(callable $formatProduct): ?array
    {
        return null;
    }
}
