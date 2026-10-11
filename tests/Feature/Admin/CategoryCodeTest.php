<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoryCodeTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('secret123'),
        ]);
    }

    public function test_store_normalizes_code_to_uppercase(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Électronique',
            'code' => 'elec',
            'is_active' => true,
            'is_featured' => false,
            'order' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Électronique', 'code' => 'ELEC']);
    }

    public function test_store_rejects_code_with_invalid_characters(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Électronique',
            'code' => 'EL C!',
            'is_active' => true,
            'is_featured' => false,
            'order' => 0,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_store_allows_empty_code(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Électronique',
            'is_active' => true,
            'is_featured' => false,
            'order' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Électronique', 'code' => null]);
    }

    public function test_store_does_not_block_duplicate_code(): void
    {
        $admin = $this->makeAdmin();
        Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'code' => 'ELEC']);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Électroménager',
            'code' => 'ELEC',
            'is_active' => true,
            'is_featured' => false,
            'order' => 0,
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors('code');
        $this->assertDatabaseHas('categories', ['name' => 'Électroménager', 'code' => 'ELEC']);
    }
}
