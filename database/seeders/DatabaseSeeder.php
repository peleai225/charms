<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer un admin
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@chamse.fr',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Exécuter les seeders
        $this->call([
            SettingsSeeder::class,
            CategorySeeder::class,
            AttributeSeeder::class,
            AccountingSeeder::class,
        ]);
    }
}
