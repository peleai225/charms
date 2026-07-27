<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    // ================================================================
    // isValid()
    // ================================================================

    public function test_is_valid_returns_true_for_valid_coupon(): void
    {
        $coupon = Coupon::create([
            'code'       => 'VALID10',
            'name'       => 'Test valide',
            'type'       => 'percentage',
            'value'      => 10,
            'is_active'  => true,
            'usage_count' => 0,
        ]);

        $this->assertTrue($coupon->isValid());
    }

    public function test_is_valid_returns_false_when_expired(): void
    {
        $coupon = Coupon::create([
            'code'       => 'EXPIRED10',
            'name'       => 'Coupon expiré',
            'type'       => 'percentage',
            'value'      => 10,
            'is_active'  => true,
            'expires_at' => now()->subDay(),
            'usage_count' => 0,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_is_valid_returns_false_when_usage_limit_reached(): void
    {
        $coupon = Coupon::create([
            'code'        => 'MAXED10',
            'name'        => 'Coupon épuisé',
            'type'        => 'percentage',
            'value'       => 10,
            'is_active'   => true,
            'usage_limit' => 5,
            'usage_count' => 5,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_is_valid_returns_false_when_inactive(): void
    {
        $coupon = Coupon::create([
            'code'       => 'INACTIVE',
            'name'       => 'Coupon inactif',
            'type'       => 'fixed',
            'value'      => 500,
            'is_active'  => false,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    // ================================================================
    // calculateDiscount()
    // ================================================================

    public function test_calculate_discount_percentage(): void
    {
        $coupon = Coupon::create([
            'code'       => 'PROMO20',
            'name'       => 'Remise 20%',
            'type'       => 'percentage',
            'value'      => 20,
            'is_active'  => true,
        ]);

        // 20 % de 10 000 = 2 000
        $discount = $coupon->calculateDiscount(10000);

        $this->assertEquals(2000.0, $discount);
    }

    public function test_calculate_discount_fixed(): void
    {
        $coupon = Coupon::create([
            'code'      => 'FIXED500',
            'name'      => 'Remise fixe 500 FCFA',
            'type'      => 'fixed',
            'value'     => 500,
            'is_active' => true,
        ]);

        $discount = $coupon->calculateDiscount(3000);

        $this->assertEquals(500.0, $discount);
    }

    public function test_calculate_discount_fixed_cannot_exceed_order_amount(): void
    {
        $coupon = Coupon::create([
            'code'      => 'FIXED2000',
            'name'      => 'Remise fixe 2000 FCFA',
            'type'      => 'fixed',
            'value'     => 2000,
            'is_active' => true,
        ]);

        // La remise ne peut pas dépasser le montant de la commande
        $discount = $coupon->calculateDiscount(1000);

        $this->assertEquals(1000.0, $discount);
    }

    public function test_calculate_discount_percentage_capped_by_max_discount(): void
    {
        $coupon = Coupon::create([
            'code'               => 'CAP50',
            'name'               => '50% plafonné à 1000',
            'type'               => 'percentage',
            'value'              => 50,
            'max_discount_amount' => 1000,
            'is_active'          => true,
        ]);

        // 50 % de 5000 = 2500, mais plafonné à 1000
        $discount = $coupon->calculateDiscount(5000);

        $this->assertEquals(1000.0, $discount);
    }

    // ================================================================
    // canBeUsedBy()
    // ================================================================

    public function test_can_be_used_by_returns_invalid_when_order_amount_too_low(): void
    {
        $coupon = Coupon::create([
            'code'             => 'MIN5000',
            'name'             => 'Minimum 5000 FCFA',
            'type'             => 'percentage',
            'value'            => 10,
            'min_order_amount' => 5000,
            'is_active'        => true,
        ]);

        $result = $coupon->canBeUsedBy(null, 3000);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['message']);
    }

    public function test_can_be_used_by_returns_valid_when_amount_meets_minimum(): void
    {
        $coupon = Coupon::create([
            'code'             => 'MIN2000',
            'name'             => 'Minimum 2000 FCFA',
            'type'             => 'percentage',
            'value'            => 10,
            'min_order_amount' => 2000,
            'is_active'        => true,
        ]);

        $result = $coupon->canBeUsedBy(null, 5000);

        $this->assertTrue($result['valid']);
    }

    public function test_can_be_used_by_returns_invalid_when_coupon_is_not_valid(): void
    {
        $coupon = Coupon::create([
            'code'       => 'EXPIREDX',
            'name'       => 'Expiré',
            'type'       => 'fixed',
            'value'      => 500,
            'is_active'  => true,
            'expires_at' => now()->subDay(),
        ]);

        $result = $coupon->canBeUsedBy(null, 10000);

        $this->assertFalse($result['valid']);
    }
}
