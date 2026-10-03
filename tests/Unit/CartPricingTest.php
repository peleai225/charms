<?php

namespace Tests\Unit;

use App\Models\Promotion;
use App\Support\CartPricing;
use App\Support\PricedLine;
use PHPUnit\Framework\TestCase;

class CartPricingTest extends TestCase
{
    private function promotion(bool $stackable = false): Promotion
    {
        return new Promotion([
            'name' => 'Offre test',
            'lot_qty' => 3,
            'lot_price' => 10000,
            'stackable_with_coupons' => $stackable,
        ]);
    }

    // ================================================================
    // PricedLine
    // ================================================================

    public function test_line_catalog_total_ignores_discount(): void
    {
        $line = new PricedLine(1, 3, 4000, 2000);

        $this->assertSame(12000.0, $line->catalogTotal());
    }

    public function test_line_total_subtracts_discount(): void
    {
        $line = new PricedLine(1, 3, 4000, 2000);

        $this->assertSame(10000.0, $line->lineTotal());
    }

    // ================================================================
    // CartPricing — totaux
    // ================================================================

    public function test_subtotal_is_catalog_sum(): void
    {
        $pricing = new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000),
            2 => new PricedLine(2, 1, 3000, 0),
        ]);

        $this->assertSame(15000.0, $pricing->subtotal());
    }

    public function test_bundle_discount_sums_line_discounts(): void
    {
        $pricing = new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000),
            2 => new PricedLine(2, 1, 3000, 0),
        ]);

        $this->assertSame(2000.0, $pricing->bundleDiscount());
    }

    public function test_payable_subtotal_is_net_of_bundle(): void
    {
        $pricing = new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000),
            2 => new PricedLine(2, 1, 3000, 0),
        ]);

        $this->assertSame(13000.0, $pricing->payableSubtotal());
    }

    // ================================================================
    // CartPricing — base du coupon
    // ================================================================

    public function test_coupon_base_excludes_promoted_lines(): void
    {
        $pricing = new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000, $this->promotion()),
            2 => new PricedLine(2, 1, 3000, 0),
        ]);

        $this->assertSame(3000.0, $pricing->couponEligibleBase());
    }

    public function test_coupon_base_includes_stackable_promoted_lines(): void
    {
        $pricing = new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000, $this->promotion(stackable: true)),
            2 => new PricedLine(2, 1, 3000, 0),
        ]);

        $this->assertSame(13000.0, $pricing->couponEligibleBase());
    }

    // ================================================================
    // CartPricing — total
    // ================================================================

    public function test_total_subtracts_both_discounts(): void
    {
        $pricing = (new CartPricing([
            1 => new PricedLine(1, 3, 4000, 2000, $this->promotion()),
            2 => new PricedLine(2, 1, 3000, 0),
        ]))->withCouponDiscount(600);

        $this->assertSame(12400.0, $pricing->total());
    }

    public function test_total_never_goes_negative(): void
    {
        $pricing = (new CartPricing([
            1 => new PricedLine(1, 1, 1000, 1000),
        ]))->withCouponDiscount(5000);

        $this->assertSame(0.0, $pricing->total());
    }

    public function test_with_coupon_discount_preserves_lines(): void
    {
        $original = new CartPricing([1 => new PricedLine(1, 2, 5000, 0)]);
        $updated = $original->withCouponDiscount(1000);

        $this->assertSame(0.0, $original->couponDiscount);
        $this->assertSame(1000.0, $updated->couponDiscount);
        $this->assertSame(10000.0, $updated->subtotal());
    }
}
