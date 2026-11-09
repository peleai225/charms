<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Haut de l'accueil : menu des catégories à gauche, grande bannière, petites cartes « home_side » à droite.
 */
class HomeTopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-07 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_the_side_cards_reach_the_home_page_in_order(): void
    {
        Banner::create(['name' => 'Carte 2', 'position' => 'home_side', 'type' => 'promo', 'image' => 'banners/s2.jpg', 'is_active' => true, 'order' => 2]);
        Banner::create([
            'name' => 'Carte 1', 'position' => 'home_side', 'type' => 'promo', 'is_active' => true, 'order' => 1,
            'image' => 'banners/s1.png', 'image_mobile' => 'banners/s1-m.png',
            'title' => 'Rentrée scolaire', 'subtitle' => 'Jusqu\'au 30 novembre', 'link' => '/categorie/fournitures',
            'button_text' => 'J\'en profite', 'background_color' => '#FDE68A', 'text_color' => '#111111',
        ]);
        Banner::create(['name' => 'Inactive', 'position' => 'home_side', 'type' => 'promo', 'image' => 'banners/x.jpg', 'is_active' => false]);
        Banner::create(['name' => 'Terminée', 'position' => 'home_side', 'type' => 'promo', 'image' => 'banners/y.jpg', 'is_active' => true,
            'ends_at' => '2026-11-01 00:00:00']);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('banners.home_side', 2)
            ->where('banners.home_side.0.title', 'Rentrée scolaire')
            ->where('banners.home_side.0.subtitle', 'Jusqu\'au 30 novembre')
            ->where('banners.home_side.0.link', '/categorie/fournitures')
            ->where('banners.home_side.0.button_text', 'J\'en profite')
            ->where('banners.home_side.0.background_color', '#FDE68A')
            ->where('banners.home_side.0.text_color', '#111111')
            ->where('banners.home_side.0.image', asset('storage/banners/s1.png'))
            ->where('banners.home_side.0.image_mobile', asset('storage/banners/s1-m.png'))
            ->where('banners.home_side.1.image', asset('storage/banners/s2.jpg')));
    }

    public function test_the_category_menu_lists_active_main_categories_with_icon_and_subcategories(): void
    {
        $mode = Category::create(['name' => 'Mode', 'slug' => 'mode', 'icon' => 'shirt', 'is_active' => true, 'order' => 2]);
        $maison = Category::create(['name' => 'Maison', 'slug' => 'maison', 'is_active' => true, 'order' => 1]);
        Category::create(['name' => 'Archives', 'slug' => 'archives', 'is_active' => false, 'order' => 0]);
        $robes = Category::create(['name' => 'Robes', 'slug' => 'robes', 'parent_id' => $mode->id, 'is_active' => true, 'order' => 1]);
        Category::create(['name' => 'Ancienne collection', 'slug' => 'ancienne', 'parent_id' => $mode->id, 'is_active' => false]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('menu_categories', [
                ['id' => $maison->id, 'name' => 'Maison', 'slug' => 'maison', 'icon' => null, 'children' => []],
                ['id' => $mode->id, 'name' => 'Mode', 'slug' => 'mode', 'icon' => 'shirt', 'children' => [
                    ['id' => $robes->id, 'name' => 'Robes', 'slug' => 'robes'],
                ]],
            ]));
    }
}
