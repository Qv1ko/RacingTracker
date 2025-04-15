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
            ['name' => 'Arrows', 'nationality' => 'British', 'status' => false],
            ['name' => 'BAR', 'nationality' => 'British', 'status' => false],
            ['name' => 'Benetton', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Ferrari', 'nationality' => 'Italian', 'status' => true],
            ['name' => 'Jaguar', 'nationality' => 'British', 'status' => false],
            ['name' => 'Jordan', 'nationality' => 'British', 'status' => false],
            ['name' => 'McLaren', 'nationality' => 'British', 'status' => true],
            ['name' => 'Minardi', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Prost', 'nationality' => 'French', 'status' => false],
            ['name' => 'Sauber', 'nationality' => 'Swiss', 'status' => false],
            ['name' => 'Williams', 'nationality' => 'British', 'status' => true],
        ]);
    }
}
