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
            ['name' => 'Alpine', 'nationality' => 'French', 'status' => true],
            ['name' => 'Arrows', 'nationality' => 'British', 'status' => false],
            ['name' => 'Aston Martin', 'nationality' => 'British', 'status' => true],
            ['name' => 'BAR', 'nationality' => 'British', 'status' => false],
            ['name' => 'Benetton', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Brawn GP', 'nationality' => 'British', 'status' => false],
            ['name' => 'Ferrari', 'nationality' => 'Italian', 'status' => true],
            ['name' => 'Haas', 'nationality' => 'American', 'status' => false],
            ['name' => 'HRT', 'nationality' => 'Spanish', 'status' => false],
            ['name' => 'Jaguar', 'nationality' => 'British', 'status' => false],
            ['name' => 'Lotus', 'nationality' => 'British', 'status' => false],
            ['name' => 'McLaren', 'nationality' => 'British', 'status' => true],
            ['name' => 'Mercedes', 'nationality' => 'German', 'status' => true],
            ['name' => 'Minardi', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Prost', 'nationality' => 'French', 'status' => false],
            ['name' => 'Red Bull Racing', 'nationality' => 'Austrian', 'status' => true],
            ['name' => 'Renault', 'nationality' => 'French', 'status' => false],
            ['name' => 'Sauber', 'nationality' => 'Swiss', 'status' => false],
            ['name' => 'Toyota', 'nationality' => 'Japanese', 'status' => false],
            ['name' => 'Williams', 'nationality' => 'British', 'status' => true],
        ]);
    }
}
