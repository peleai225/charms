<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminProductEnrichTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function product(string $name, ?string $short, ?string $description): Product
    {
        return Product::create([
            'name' => $name,
            'sale_price' => 5000,
            'status' => 'active',
            'short_description' => $short,
            'description' => $description,
        ]);
    }

    public function test_incomplete_filter_lists_only_products_with_a_missing_description(): void
    {
        $complete = $this->product('Sac complet', 'Un joli sac.', 'Sac en toile, deux poches.');
        $noShort = $this->product('Ballerines', null, 'Ballerines plates.');
        $noLong = $this->product('Casquette', 'Casquette réglable.', '');

        $this->actingAs($this->admin)
            ->get(route('admin.products.index').'?fiche=incomplete')
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.fiche', 'incomplete')
                ->where('products.data', fn ($rows) => collect($rows)->pluck('id')->sort()->values()->all()
                    === collect([$noShort->id, $noLong->id])->sort()->values()->all()));

        $this->assertNotNull($complete->id);
    }

    public function test_admin_can_apply_reviewed_descriptions_to_several_products(): void
    {
        $a = $this->product('Ballerines', null, null);
        $b = $this->product('Casquette', 'Ancienne accroche', null);

        $this->actingAs($this->admin)
            ->post(route('admin.ai.apply-descriptions'), [
                'updates' => [
                    ['id' => $a->id, 'short_description' => 'Ballerines confortables pour tous les jours.', 'description' => 'Semelle souple, bout rond.'],
                    ['id' => $b->id, 'short_description' => 'Casquette réglable en coton.', 'description' => 'Visière courbée.'],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $a->refresh();
        $b->refresh();
        $this->assertSame('Ballerines confortables pour tous les jours.', $a->short_description);
        $this->assertSame('Semelle souple, bout rond.', $a->description);
        $this->assertSame('Casquette réglable en coton.', $b->short_description);
        $this->assertSame('Ballerines', $a->name);
    }

    public function test_applying_descriptions_rejects_an_unknown_product(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.ai.apply-descriptions'), [
                'updates' => [['id' => 999999, 'short_description' => 'x', 'description' => 'y']],
            ])
            ->assertSessionHasErrors('updates.0.id');
    }
}
