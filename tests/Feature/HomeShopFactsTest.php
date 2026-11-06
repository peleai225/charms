<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Accueil sobre : les infos affichées sous la bannière viennent des réglages de la boutique
 * (pas de promesses toutes faites), et « Nouveau » ne s'affiche que sur les produits récents.
 */
class HomeShopFactsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-06 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function settings(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::set($key, $value);
        }
        Setting::clearCache();
    }

    public function test_the_home_page_shows_the_real_delivery_payment_and_contact_details(): void
    {
        $this->settings([
            'payment_cod_enabled' => '1',
            'social_whatsapp' => '+225 0707070707',
            'shipping_zones' => json_encode([
                ['name' => 'Abidjan', 'cities' => 'Cocody, Yopougon', 'price' => 1500],
                ['name' => 'Bouaké', 'cities' => '', 'price' => 3000],
                ['name' => 'Yamoussoukro', 'cities' => '', 'price' => 3000],
                ['name' => 'San-Pédro', 'cities' => '', 'price' => 3500],
            ]),
        ]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('shop_facts', [
                ['key' => 'delivery', 'title' => 'Livraison dès 1 500 F CFA', 'desc' => 'Abidjan, Bouaké, Yamoussoukro et 1 autre zone', 'href' => null],
                ['key' => 'cod', 'title' => 'Paiement à la livraison', 'desc' => 'Vous payez en recevant votre colis', 'href' => null],
                ['key' => 'whatsapp', 'title' => 'Une question ?', 'desc' => 'WhatsApp +225 07 07 07 07 07', 'href' => 'https://wa.me/2250707070707'],
            ]));
    }

    public function test_nothing_is_promised_when_the_shop_has_not_configured_it(): void
    {
        $this->settings(['payment_cod_enabled' => '0']);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->where('shop_facts', []));
    }

    public function test_free_delivery_zones_do_not_announce_a_price(): void
    {
        $this->settings([
            'payment_cod_enabled' => '0',
            'shipping_zones' => json_encode([['name' => 'Abidjan', 'cities' => '', 'price' => 0]]),
        ]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('shop_facts.0.title', 'Livraison')
            ->where('shop_facts.0.desc', 'Abidjan'));
    }

    public function test_the_new_badge_only_shows_on_products_added_in_the_last_30_days(): void
    {
        $recent = Product::create(['name' => 'Pyjama', 'sale_price' => 5000, 'status' => 'active', 'is_new' => true]);
        $old = Product::create(['name' => 'Bonnet', 'sale_price' => 2000, 'status' => 'active', 'is_new' => true]);
        $old->forceFill(['created_at' => now()->subDays(45)])->save();

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('featured_products', fn ($products) => collect($products)->firstWhere('id', $recent->id)['is_new'] === true
                && collect($products)->firstWhere('id', $old->id)['is_new'] === false));
    }
}
