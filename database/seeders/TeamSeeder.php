<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Team::insert([
            ['name' => 'Alpine', 'nationality' => 'French', 'is_active' => true],
            ['name' => 'Arrows', 'nationality' => 'British', 'is_active' => false],
            ['name' => 'Aston Martin', 'nationality' => 'British', 'is_active' => true],
            ['name' => 'BAR', 'nationality' => 'British', 'is_active' => false],
            ['name' => 'Benetton', 'nationality' => 'Italian', 'is_active' => false],
            ['name' => 'Brawn GP', 'nationality' => 'British', 'is_active' => false],
            ['name' => 'Ferrari', 'nationality' => 'Italian', 'is_active' => true],
            ['name' => 'Haas', 'nationality' => 'American', 'is_active' => false],
            ['name' => 'HRT', 'nationality' => 'Spanish', 'is_active' => false],
            ['name' => 'Jaguar', 'nationality' => 'British', 'is_active' => false],
            ['name' => 'Lotus', 'nationality' => 'British', 'is_active' => false],
            ['name' => 'McLaren', 'nationality' => 'British', 'is_active' => true],
            ['name' => 'Mercedes', 'nationality' => 'German', 'is_active' => true],
            ['name' => 'Minardi', 'nationality' => 'Italian', 'is_active' => false],
            ['name' => 'Prost', 'nationality' => 'French', 'is_active' => false],
            ['name' => 'Red Bull Racing', 'nationality' => 'Austrian', 'is_active' => true],
            ['name' => 'Renault', 'nationality' => 'French', 'is_active' => false],
            ['name' => 'Sauber', 'nationality' => 'Swiss', 'is_active' => false],
            ['name' => 'Toyota', 'nationality' => 'Japanese', 'is_active' => false],
            ['name' => 'Williams', 'nationality' => 'British', 'is_active' => true],
        ]);
    }
}
