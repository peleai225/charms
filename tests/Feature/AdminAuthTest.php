<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crée un utilisateur admin actif avec le mot de passe fourni.
     */
    private function makeAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role'      => 'admin',
            'is_active' => true,
            'password'  => Hash::make('secret123'),
        ], $overrides));
    }

    // ================================================================
    // Connexion réussie → redirection vers dashboard
    // ================================================================

    public function test_admin_can_login_with_correct_credentials(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->post(route('admin.login.post'), [
            'email'    => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    // ================================================================
    // Mauvais mot de passe → erreur de validation
    // ================================================================

    public function test_admin_login_fails_with_wrong_password(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->post(route('admin.login.post'), [
            'email'    => $admin->email,
            'password' => 'mauvais_mdp',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ================================================================
    // Compte désactivé → rejeté
    // ================================================================

    public function test_inactive_admin_cannot_login(): void
    {
        $admin = $this->makeAdmin(['is_active' => false]);

        $response = $this->post(route('admin.login.post'), [
            'email'    => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ================================================================
    // Rôle non-admin → rejeté
    // ================================================================

    public function test_customer_user_cannot_login_as_admin(): void
    {
        $user = User::factory()->create([
            'role'      => 'customer',
            'is_active' => true,
            'password'  => Hash::make('secret123'),
        ]);

        $response = $this->post(route('admin.login.post'), [
            'email'    => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ================================================================
    // Throttle : 5 tentatives échouées → la 6ème retourne 429
    // ================================================================

    public function test_too_many_failed_login_attempts_returns_429(): void
    {
        $admin = $this->makeAdmin();

        // 5 tentatives avec mauvais mot de passe
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.post'), [
                'email'    => $admin->email,
                'password' => 'wrong_password',
            ]);
        }

        // La 6ème tentative doit être bloquée par le rate limiter
        $response = $this->post(route('admin.login.post'), [
            'email'    => $admin->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(429);
    }
}
