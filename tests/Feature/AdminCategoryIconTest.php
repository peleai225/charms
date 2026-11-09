<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Support\CategoryIcons;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Admin → Catégories : icône choisie dans une liste fermée (App\Support\CategoryIcons),
 * affichée dans le menu des catégories et « Nos univers populaires » de l'accueil.
 */
class AdminCategoryIconTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_a_category_is_created_with_an_icon_from_the_list(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => 'Mode', 'icon' => 'shirt', 'is_active' => true])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame('shirt', Category::where('name', 'Mode')->value('icon'));
    }

    public function test_a_category_can_be_created_without_an_icon(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => 'Divers', 'icon' => ''])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();

        $this->assertNull(Category::where('name', 'Divers')->value('icon'));
    }

    public function test_an_icon_outside_the_list_is_refused_at_creation(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), ['name' => 'Mode', 'icon' => '<script>'])
            ->assertSessionHasErrors('icon');

        $this->assertSame(0, Category::count());
    }

    public function test_the_icon_of_a_category_can_be_changed_and_removed(): void
    {
        $category = Category::create(['name' => 'Maison', 'icon' => 'house']);

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), ['name' => 'Maison', 'icon' => 'sofa'])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();
        $this->assertSame('sofa', $category->fresh()->icon);

        // Comme le formulaire de l'admin : tous les champs, « Aucune » icône = valeur vide
        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Maison', 'parent_id' => '', 'description' => '', 'order' => 0,
                'is_active' => true, 'is_featured' => false, 'icon' => '',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();
        $this->assertNull($category->fresh()->icon);
    }

    public function test_an_icon_outside_the_list_is_refused_on_update(): void
    {
        $category = Category::create(['name' => 'Maison', 'icon' => 'house']);

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), ['name' => 'Maison', 'icon' => 'rocket'])
            ->assertSessionHasErrors('icon');

        $this->assertSame('house', $category->fresh()->icon);
    }

    public function test_the_categories_page_lists_the_icons_and_the_current_icon(): void
    {
        Category::create(['name' => 'Maison', 'icon' => 'house']);

        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Categories/Index')
                ->where('categories.0.icon', 'house')
                ->has('icon_options', count(CategoryIcons::ICONS))
                ->where('icon_options.0', ['key' => 'shirt', 'label' => 'Mode & vêtements']));
    }
}
