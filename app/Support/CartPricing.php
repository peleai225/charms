<?php

namespace App\Support;

use App\Models\CartItem;
use RuntimeException;

/**
 * Totaux d'un panier. Immuable : le moteur construit d'abord les lignes, puis
 * dérive la remise coupon de la base éligible via withCouponDiscount().
 */
final class CartPricing
{
    /**
     * @param  array<int, PricedLine>  $lines  indexé par cart_item_id
     */
    public function __construct(
        public readonly array $lines,
        public readonly float $couponDiscount = 0.0,
    ) {}

    /** Somme catalogue, avant toute remise. */
    public function subtotal(): float
    {
        return array_sum(array_map(fn (PricedLine $line) => $line->catalogTotal(), $this->lines));
    }

    public function bundleDiscount(): float
    {
        return array_sum(array_map(fn (PricedLine $line) => $line->discount, $this->lines));
    }

    /** Ce que le client paie avant coupon, frais de port et taxes. */
    public function payableSubtotal(): float
    {
        return $this->subtotal() - $this->bundleDiscount();
    }

    /**
     * Assiette du coupon. Les lignes en offre en sont exclues, sauf si l'offre
     * est explicitement déclarée cumulable.
     */
    public function couponEligibleBase(): float
    {
        return array_sum(array_map(
            fn (PricedLine $line) => $line->promotion === null || $line->promotion->stackable_with_coupons
                ? $line->lineTotal()
                : 0.0,
            $this->lines
        ));
    }

    public function total(): float
    {
        return max(0, $this->payableSubtotal() - $this->couponDiscount);
    }

    public function lineFor(CartItem $item): PricedLine
    {
        if (! isset($this->lines[$item->id])) {
            throw new RuntimeException("Aucune ligne tarifée pour l'article de panier {$item->id}.");
        }

        return $this->lines[$item->id];
    }

    public function withCouponDiscount(float $discount): self
    {
        return new self($this->lines, $discount);
    }
}
