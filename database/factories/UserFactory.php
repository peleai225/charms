<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $password = static::$password ??= Hash::make('password');
        $remember = Str::random(10);

        if ($this->faker !== null) {
            return [
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                'email_verified_at' => now(),
                'password' => $password,
                'remember_token' => $remember,
                'role' => 'customer',
                'is_active' => true,
            ];
        }

        $suffix = Str::lower(Str::random(12));

        return [
            'name' => 'User '.$suffix,
            'email' => 'user.'.$suffix.'@seed.local',
            'email_verified_at' => now(),
            'password' => $password,
            'remember_token' => $remember,
            'role' => 'customer',
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
