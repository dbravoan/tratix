<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_admin' => true,
                'plan' => 'pro',
            ]
        );

        // Optional demo admin with LATAM visibility (only if it does not exist).
        if (! User::where('email', 'admin@contratos.local')->exists()) {
            User::factory()->create([
                'name' => 'Admin Contratos',
                'email' => 'admin@contratos.local',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'plan' => 'pro',
            ]);
        }

        $this->command?->info("Usuario de prueba: {$admin->email} / password");

        $this->call(DocumentRequirementSeeder::class);
    }
}
