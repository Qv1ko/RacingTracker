<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Team::insert([
            ['name' => 'Arrows', 'nationality' => 'British', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'BAR', 'nationality' => 'British', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Benetton', 'nationality' => 'Italian', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Ferrari', 'nationality' => 'Italian', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Jaguar', 'nationality' => 'British', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Jordan', 'nationality' => 'British', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'McLaren', 'nationality' => 'British', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Minardi', 'nationality' => 'Italian', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Prost', 'nationality' => 'French', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Sauber', 'nationality' => 'Swiss', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Williams', 'nationality' => 'British', 'updated_at' => now(), 'created_at' => now()],
        ]);
    }
}
