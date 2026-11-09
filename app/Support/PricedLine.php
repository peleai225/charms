<?php

namespace App\Support;

use App\Models\Promotion;

/**
 * Une ligne de panier tarifée. Le prix unitaire reste le prix catalogue ;
 * la remise de lot est portée à part, ce qui permet d'afficher « 10 000 F
 * au lieu de 12 000 » sans avoir détruit le prix d'origine.
 *
 * Exception : en vente flash, le prix unitaire est déjà le prix flash
 * (flashSaleId renseigné). Une telle ligne n'entre ni dans les offres par lot
 * ni dans l'assiette des codes promo.
 */
final class PricedLine
{
    public function __construct(
        public readonly int $cartItemId,
        public readonly int $quantity,
        public readonly float $unitPrice,
        public readonly float $discount,
        public readonly ?Promotion $promotion = null,
        public readonly ?int $flashSaleId = null,
    ) {}

    public function isFlashSale(): bool
    {
        return $this->flashSaleId !== null;
    }

    public function catalogTotal(): float
    {
        return $this->unitPrice * $this->quantity;
    }

    public function lineTotal(): float
    {
        return $this->catalogTotal() - $this->discount;
    }
}
