<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Team;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@racingtracker.dev',
            'password' => bcrypt('admin'),
        ]);

        $this->call([
            // SeasonSeeder::class,
            // DriverSeeder::class,
            // TeamSeeder::class,
        ]);
    }
}
