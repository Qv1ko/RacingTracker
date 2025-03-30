<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Driver::insert([
            ['name' => 'Jean', 'surname' => 'Alesi', 'nationality' => 'French', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Rubens', 'surname' => 'Barrichello', 'nationality' => 'Brazilian', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Luciano', 'surname' => 'Burti', 'nationality' => 'Brazilian', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Jenson', 'surname' => 'Button', 'nationality' => 'British', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'David', 'surname' => 'Coulthard', 'nationality' => 'British', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Pedro', 'surname' => 'de la Rosa', 'nationality' => 'Spanish', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Giancarlo', 'surname' => 'Fisichella', 'nationality' => 'Italian', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Heinz-Harald', 'surname' => 'Frentzen', 'nationality' => 'German', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Marc', 'surname' => 'Gené', 'nationality' => 'Spanish', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Mika', 'surname' => 'Häkkinen', 'nationality' => 'Finnish', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Nick', 'surname' => 'Heidfeld', 'nationality' => 'German', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Johnny', 'surname' => 'Herbert', 'nationality' => 'British', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Eddie', 'surname' => 'Irvine', 'nationality' => 'British', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Gastón', 'surname' => 'Mazzacane', 'nationality' => 'Argentine', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Mika', 'surname' => 'Salo', 'nationality' => 'Finnish', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Michael', 'surname' => 'Schumacher', 'nationality' => 'German', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Ralf', 'surname' => 'Schumacher', 'nationality' => 'German', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Jarno', 'surname' => 'Trulli', 'nationality' => 'Italian', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Jos', 'surname' => 'Verstappen', 'nationality' => 'Dutch', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Jacques', 'surname' => 'Villeneuve', 'nationality' => 'Canadian', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Alexander', 'surname' => 'Wurz', 'nationality' => 'Austrian', 'updated_at' => now(), 'created_at' => now()],
            ['name' => 'Ricardo', 'surname' => 'Zonta', 'nationality' => 'Brazilian', 'updated_at' => now(), 'created_at' => now()],
        ]);
    }
}
