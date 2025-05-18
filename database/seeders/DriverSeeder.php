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
            ['name' => 'Fernando', 'surname' => 'Alonso', 'nationality' => 'Spanish', 'status' => true],
            ['name' => 'Alexander', 'surname' => 'Albon', 'nationality' => 'Thai', 'status' => true],
            ['name' => 'Rubens', 'surname' => 'Barrichello', 'nationality' => 'Brazilian', 'status' => true],
            ['name' => 'Valtteri', 'surname' => 'Bottas', 'nationality' => 'Finnish', 'status' => false],
            ['name' => 'Jenson', 'surname' => 'Button', 'nationality' => 'British', 'status' => true],
            ['name' => 'David', 'surname' => 'Coulthard', 'nationality' => 'British', 'status' => false],
            ['name' => 'Mike', 'surname' => 'Conway', 'nationality' => 'British', 'status' => false],
            ['name' => 'Pedro', 'surname' => 'de la Rosa', 'nationality' => 'Spanish', 'status' => false],
            ['name' => 'Giancarlo', 'surname' => 'Fisichella', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Marc', 'surname' => 'Gené', 'nationality' => 'Spanish', 'status' => true],
            ['name' => 'Pierre', 'surname' => 'Gasly', 'nationality' => 'French', 'status' => true],
            ['name' => 'Zhou', 'surname' => 'Guanyu', 'nationality' => 'Chinese', 'status' => true],
            ['name' => 'Esteban', 'surname' => 'Gutiérrez', 'nationality' => 'Mexican', 'status' => false],
            ['name' => 'Mika', 'surname' => 'Häkkinen', 'nationality' => 'Finnish', 'status' => false],
            ['name' => 'Johnny', 'surname' => 'Herbert', 'nationality' => 'British', 'status' => false],
            ['name' => 'Lewis', 'surname' => 'Hamilton', 'nationality' => 'British', 'status' => true],
            ['name' => 'Nico', 'surname' => 'Hülkenberg', 'nationality' => 'German', 'status' => false],
            ['name' => 'Robert', 'surname' => 'Kubica', 'nationality' => 'Polish', 'status' => false],
            ['name' => 'Charles', 'surname' => 'Leclerc', 'nationality' => 'Monégasque', 'status' => true],
            ['name' => 'Felipe', 'surname' => 'Massa', 'nationality' => 'Brazilian', 'status' => false],
            ['name' => 'Pastor', 'surname' => 'Maldonado', 'nationality' => 'Venezuelan', 'status' => false],
            ['name' => 'Lando', 'surname' => 'Norris', 'nationality' => 'British', 'status' => true],
            ['name' => 'Esteban', 'surname' => 'Ocon', 'nationality' => 'French', 'status' => true],
            ['name' => 'Jolyon', 'surname' => 'Palmer', 'nationality' => 'British', 'status' => false],
            ['name' => 'Sergio', 'surname' => 'Perez', 'nationality' => 'Mexican', 'status' => true],
            ['name' => 'Oscar', 'surname' => 'Piastri', 'nationality' => 'Australian', 'status' => true],
            ['name' => 'Carlos', 'surname' => 'Sainz', 'nationality' => 'Spanish', 'status' => true],
            ['name' => 'Bruno', 'surname' => 'Senna', 'nationality' => 'Brazilian', 'status' => false],
            ['name' => 'Michael', 'surname' => 'Schumacher', 'nationality' => 'German', 'status' => false],
            ['name' => 'Lance', 'surname' => 'Stroll', 'nationality' => 'Canadian', 'status' => true],
            ['name' => 'Jarno', 'surname' => 'Trulli', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Yuki', 'surname' => 'Tsunoda', 'nationality' => 'Japanese', 'status' => true],
            ['name' => 'Kimi', 'surname' => 'Räikkönen', 'nationality' => 'Finnish', 'status' => false],
            ['name' => 'Nico', 'surname' => 'Rosberg', 'nationality' => 'German', 'status' => false],
            ['name' => 'George', 'surname' => 'Russell', 'nationality' => 'British', 'status' => true],
            ['name' => 'Max', 'surname' => 'Verstappen', 'nationality' => 'Dutch', 'status' => true],
            ['name' => 'Sebastian', 'surname' => 'Vettel', 'nationality' => 'German', 'status' => false],
            ['name' => 'Mark', 'surname' => 'Webber', 'nationality' => 'Australian', 'status' => false],
        ]);
    }
}
