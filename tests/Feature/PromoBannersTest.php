<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PromoBannersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Carbon::setTestNow('2026-12-20 10:00:00');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function bannerPayload(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'Soldes de Noël',
            'image' => UploadedFile::fake()->image('desktop.jpg', 1920, 600),
            'position' => 'home_hero',
            'type' => 'hero',
            'is_active' => true,
        ];
    }

    public function test_admin_creates_a_banner_with_end_time_mobile_image_colors_and_countdown(): void
    {
        $this->actingAs($this->admin)->post(route('admin.banners.store'), $this->bannerPayload([
            'image_mobile' => UploadedFile::fake()->image('mobile.jpg', 800, 1000),
            'description' => 'Jusqu\'à -50 % sur la mode',
            'background_color' => '#111111',
            'text_color' => '#FFFFFF',
            'ends_at' => '2026-12-24T18:30',
            'show_countdown' => true,
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $banner = Banner::first();
        $this->assertSame('2026-12-24 18:30:00', $banner->ends_at->format('Y-m-d H:i:s'));
        $this->assertTrue($banner->show_countdown);
        $this->assertSame('#111111', $banner->background_color);
        $this->assertSame('#FFFFFF', $banner->text_color);
        $this->assertSame('Jusqu\'à -50 % sur la mode', $banner->description);
        $this->assertNotNull($banner->image_mobile);
        Storage::disk('public')->assertExists($banner->image_mobile);
    }

    public function test_a_countdown_needs_an_end_date(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.banners.store'), $this->bannerPayload(['show_countdown' => true]))
            ->assertSessionHasErrors('ends_at');
    }

    public function test_an_end_date_without_time_keeps_the_banner_until_the_end_of_that_day(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.banners.store'), $this->bannerPayload(['ends_at' => '2026-12-24']))
            ->assertSessionHasNoErrors();

        $this->assertSame('2026-12-24 23:59:59', Banner::first()->ends_at->format('Y-m-d H:i:s'));
    }

    public function test_positions_never_shown_on_the_site_are_not_offered(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.banners.create'))
            ->assertInertia(fn (Assert $page) => $page->where('positions', fn ($positions) => collect($positions)->keys()->sort()->values()->all()
                === ['announcement_bar', 'home_bottom', 'home_hero', 'home_middle', 'home_side', 'popup_center']));

        $this->actingAs($this->admin)
            ->post(route('admin.banners.store'), $this->bannerPayload(['position' => 'cart_bottom']))
            ->assertSessionHasErrors('position');
    }

    public function test_an_old_banner_on_a_hidden_position_can_still_be_edited(): void
    {
        $banner = Banner::create(['name' => 'Ancienne', 'position' => 'cart_bottom', 'type' => 'promo', 'image' => 'banners/x.jpg', 'is_active' => true]);

        $this->actingAs($this->admin)
            ->put(route('admin.banners.update', $banner), ['position' => 'cart_bottom', 'type' => 'promo', 'is_active' => false])
            ->assertSessionHasNoErrors();

        $this->assertFalse($banner->fresh()->is_active);
    }

    public function test_saving_a_banner_refreshes_the_site_wide_banners_immediately(): void
    {
        cache()->put('banners_global', ['announcement' => [], 'popup' => null], 120);

        $this->actingAs($this->admin)->post(route('admin.banners.store'), $this->bannerPayload([
            'position' => 'announcement_bar', 'image' => null, 'title' => 'Livraison offerte',
        ]))->assertSessionHasNoErrors();

        $this->assertFalse(cache()->has('banners_global'));
    }

    public function test_the_home_page_gets_end_times_countdowns_and_every_promo_banner(): void
    {
        Banner::create(['name' => 'Hero', 'position' => 'home_hero', 'type' => 'hero', 'image' => 'banners/h.jpg', 'is_active' => true,
            'ends_at' => '2026-12-24 18:30:00', 'show_countdown' => true]);
        Banner::create(['name' => 'Milieu 1', 'position' => 'home_middle', 'type' => 'promo', 'image' => 'banners/m1.jpg', 'is_active' => true, 'order' => 1]);
        Banner::create(['name' => 'Milieu 2', 'position' => 'home_middle', 'type' => 'promo', 'image' => 'banners/m2.jpg', 'is_active' => true, 'order' => 2]);
        Banner::create(['name' => 'Bas', 'position' => 'home_bottom', 'type' => 'promo', 'image' => 'banners/b.jpg', 'is_active' => true]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('banners.home_hero.0.show_countdown', true)
            ->where('banners.home_hero.0.ends_at', Carbon::parse('2026-12-24 18:30:00')->toIso8601String())
            ->where('banners.home_middle', fn ($b) => count($b) === 2)
            ->where('banners.home_bottom', fn ($b) => count($b) === 1)
            ->where('server_now', now()->toIso8601String()));
    }

    private function offerOnCategory(array $overrides = []): Promotion
    {
        $category = Category::create(['name' => 'T-shirts', 'slug' => 't-shirts', 'is_active' => true]);
        $promotion = Promotion::create($overrides + [
            'name' => '3 t-shirts pour 10 000',
            'lot_qty' => 3,
            'lot_price' => 10000,
            'is_active' => true,
            'expires_at' => '2026-12-22 20:00:00',
        ]);
        $promotion->categories()->attach($category->id);
        Product::create(['name' => 'T-shirt blanc', 'sale_price' => 4000, 'status' => 'active', 'category_id' => $category->id]);

        return $promotion;
    }

    public function test_the_home_page_lists_current_offers_with_their_products_and_end_time(): void
    {
        $this->offerOnCategory();

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->has('offers', 1)
            ->where('offers.0.label', '3 pour 10 000 F CFA')
            ->where('offers.0.ends_at', Carbon::parse('2026-12-22 20:00:00')->toIso8601String())
            ->where('offers.0.category_slug', 't-shirts')
            ->where('offers.0.products', fn ($products) => collect($products)->pluck('name')->all() === ['T-shirt blanc']));
    }

    public function test_expired_offers_and_offers_without_products_are_not_listed(): void
    {
        $this->offerOnCategory(['expires_at' => '2026-12-19 10:00:00']);
        Promotion::create(['name' => 'Vide', 'lot_qty' => 2, 'lot_price' => 5000, 'is_active' => true]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page->has('offers', 0));
    }

    public function test_the_product_page_tells_when_an_offer_ends(): void
    {
        $this->offerOnCategory();
        $product = Product::first();

        $this->get(route('shop.product', $product->slug))->assertInertia(fn (Assert $page) => $page
            ->where('promotions.0.ends_at', Carbon::parse('2026-12-22 20:00:00')->toIso8601String()));
    }
}
