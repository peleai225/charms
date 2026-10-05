<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingSeenTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_rejected(): void
    {
        // Le garde admin renvoie aux invités un 200 JSON {redirect: .../admin/login}
        // (motif SPA de l'app), jamais le 204 de succès de l'endpoint.
        $response = $this->postJson(route('admin.onboarding.seen'), ['tour' => 'menus']);

        $response->assertOk();
        $this->assertStringContainsString('/admin/login', (string) $response->json('redirect'));
    }

    public function test_marks_a_tour_as_seen(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->postJson(route('admin.onboarding.seen'), ['tour' => 'menus'])
            ->assertNoContent();

        $this->assertSame(['menus'], $user->fresh()->tours_seen);
    }

    /** Review Focus nº1 — tours_seen nul au départ. */
    public function test_null_tours_seen_is_treated_as_empty(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertNull($user->tours_seen);

        $this->actingAs($user)
            ->postJson(route('admin.onboarding.seen'), ['tour' => 'products.index'])
            ->assertNoContent();

        $this->assertSame(['products.index'], $user->fresh()->tours_seen);
    }

    /** Review Focus nº3 — idempotence. */
    public function test_is_idempotent(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->tours_seen = ['menus'];
        $user->save();

        $this->actingAs($user)
            ->postJson(route('admin.onboarding.seen'), ['tour' => 'menus'])
            ->assertNoContent();

        $this->assertSame(['menus'], $user->fresh()->tours_seen);
    }

    /** Review Focus nº2 — clé inconnue. */
    public function test_rejects_unknown_tour(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->postJson(route('admin.onboarding.seen'), ['tour' => 'bogus'])
            ->assertStatus(422);

        $this->assertNull($user->fresh()->tours_seen);
    }
}
