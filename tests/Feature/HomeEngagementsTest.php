<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Bandeau d'engagements sous la grande bannière de l'accueil, géré dans
 * Admin → Paramètres → Accueil. Sans liste saisie, l'accueil garde les infos automatiques.
 */
class HomeEngagementsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function save(array $engagements)
    {
        return $this->actingAs($this->admin)->post('/admin/settings/home', ['engagements' => $engagements]);
    }

    private function item(array $overrides = []): array
    {
        return $overrides + [
            'icon' => 'truck',
            'title' => 'Livraison à Abidjan',
            'text' => 'Sous 24 à 48 h dans les communes desservies',
            'link' => '/legal/livraison',
        ];
    }

    public function test_the_settings_page_has_a_home_tab_with_the_allowed_icons(): void
    {
        $this->actingAs($this->admin)->get('/admin/settings/home')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/Index')
                ->where('activeTab', 'home')
                ->where('engagementIcons', [
                    'truck', 'shield-check', 'rotate-ccw', 'headset', 'banknote',
                    'gift', 'message-circle', 'package', 'clock', 'map-pin',
                ]));
    }

    public function test_the_admin_saves_the_engagements_in_order(): void
    {
        $this->save([
            $this->item(),
            $this->item(['icon' => 'banknote', 'title' => 'Paiement à la livraison', 'text' => '', 'link' => '']),
            $this->item(['icon' => 'headset', 'title' => 'Service client', 'text' => 'Du lundi au samedi', 'link' => 'https://wa.me/2250707070707']),
        ])->assertSessionHasNoErrors()->assertRedirect();

        Setting::clearCache();
        $this->assertSame([
            ['icon' => 'truck', 'title' => 'Livraison à Abidjan', 'text' => 'Sous 24 à 48 h dans les communes desservies', 'link' => '/legal/livraison'],
            ['icon' => 'banknote', 'title' => 'Paiement à la livraison', 'text' => null, 'link' => null],
            ['icon' => 'headset', 'title' => 'Service client', 'text' => 'Du lundi au samedi', 'link' => 'https://wa.me/2250707070707'],
        ], json_decode(Setting::get('home_engagements'), true));
    }

    public function test_the_home_page_shows_the_engagements_chosen_by_the_admin(): void
    {
        Setting::set('social_whatsapp', '+225 0707070707');
        $this->save([
            $this->item(),
            $this->item(['icon' => 'shield-check', 'title' => 'Paiement sécurisé', 'text' => '', 'link' => '']),
        ]);
        Setting::clearCache();

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('shop_facts', [
                ['key' => 'truck', 'title' => 'Livraison à Abidjan', 'desc' => 'Sous 24 à 48 h dans les communes desservies', 'href' => '/legal/livraison'],
                ['key' => 'shield-check', 'title' => 'Paiement sécurisé', 'desc' => '', 'href' => null],
            ]));
    }

    public function test_an_empty_list_brings_back_the_automatic_shop_details(): void
    {
        Setting::set('payment_cod_enabled', '1');
        $this->save([$this->item()]);
        $this->save([])->assertSessionHasNoErrors();
        Setting::clearCache();

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('shop_facts', [
                ['key' => 'cod', 'title' => 'Paiement à la livraison', 'desc' => 'Vous payez en recevant votre colis', 'href' => null],
            ]));
    }

    public function test_an_icon_outside_the_list_is_refused(): void
    {
        $this->save([$this->item(['icon' => 'skull'])])->assertSessionHasErrors('engagements.0.icon');
    }

    public function test_a_title_is_required_and_kept_short(): void
    {
        $this->save([$this->item(['title' => ''])])->assertSessionHasErrors('engagements.0.title');
        $this->save([$this->item(['title' => str_repeat('a', 61)])])->assertSessionHasErrors('engagements.0.title');
        $this->save([$this->item(['text' => str_repeat('a', 121)])])->assertSessionHasErrors('engagements.0.text');
    }

    public function test_a_link_must_be_a_site_page_or_a_web_address(): void
    {
        foreach (['javascript:alert(1)', '//evil.example', '/\evil.example', 'www.exemple.ci', 'ftp://exemple.ci'] as $link) {
            $this->save([$this->item(['link' => $link])])->assertSessionHasErrors('engagements.0.link');
        }

        $this->save([$this->item(['link' => '/boutique?on_sale=1'])])->assertSessionHasNoErrors();
    }

    public function test_at_most_six_engagements(): void
    {
        $this->save(array_fill(0, 7, $this->item()))->assertSessionHasErrors('engagements');
        $this->assertNull(Setting::where('key', 'home_engagements')->first());
    }

    public function test_a_broken_saved_value_falls_back_to_the_automatic_details(): void
    {
        Setting::set('payment_cod_enabled', '0');
        Setting::set('home_engagements', json_encode([['icon' => 'skull', 'title' => 'Bizarre'], 'texte', ['icon' => 'truck']]));
        Setting::clearCache();

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->where('shop_facts', []));
    }

    public function test_only_admins_change_the_home_settings(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->post('/admin/settings/home', ['engagements' => [$this->item()]]);

        $this->assertNull(Setting::where('key', 'home_engagements')->first());
    }
}
