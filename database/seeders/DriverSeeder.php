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
            ['name' => 'Jean', 'surname' => 'Alesi', 'nationality' => 'French', 'status' => false],
            ['name' => 'Rubens', 'surname' => 'Barrichello', 'nationality' => 'Brazilian', 'status' => true],
            ['name' => 'Luciano', 'surname' => 'Burti', 'nationality' => 'Brazilian', 'status' => false],
            ['name' => 'Jenson', 'surname' => 'Button', 'nationality' => 'British', 'status' => true],
            ['name' => 'David', 'surname' => 'Coulthard', 'nationality' => 'British', 'status' => false],
            ['name' => 'Pedro', 'surname' => 'de la Rosa', 'nationality' => 'Spanish', 'status' => false],
            ['name' => 'Giancarlo', 'surname' => 'Fisichella', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Heinz-Harald', 'surname' => 'Frentzen', 'nationality' => 'German', 'status' => false],
            ['name' => 'Marc', 'surname' => 'Gené', 'nationality' => 'Spanish', 'status' => true],
            ['name' => 'Mika', 'surname' => 'Häkkinen', 'nationality' => 'Finnish', 'status' => false],
            ['name' => 'Nick', 'surname' => 'Heidfeld', 'nationality' => 'German', 'status' => false],
            ['name' => 'Johnny', 'surname' => 'Herbert', 'nationality' => 'British', 'status' => false],
            ['name' => 'Eddie', 'surname' => 'Irvine', 'nationality' => 'British', 'status' => false],
            ['name' => 'Gastón', 'surname' => 'Mazzacane', 'nationality' => 'Argentine', 'status' => false],
            ['name' => 'Mika', 'surname' => 'Salo', 'nationality' => 'Finnish', 'status' => false],
            ['name' => 'Michael', 'surname' => 'Schumacher', 'nationality' => 'German', 'status' => false],
            ['name' => 'Ralf', 'surname' => 'Schumacher', 'nationality' => 'German', 'status' => false],
            ['name' => 'Jarno', 'surname' => 'Trulli', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Jos', 'surname' => 'Verstappen', 'nationality' => 'Dutch', 'status' => false],
            ['name' => 'Jacques', 'surname' => 'Villeneuve', 'nationality' => 'Canadian', 'status' => false],
            ['name' => 'Alexander', 'surname' => 'Wurz', 'nationality' => 'Austrian', 'status' => false],
            ['name' => 'Ricardo', 'surname' => 'Zonta', 'nationality' => 'Brazilian', 'status' => false],
        ]);
    }
}
