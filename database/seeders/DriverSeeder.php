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
            ['name' => 'Fernando', 'surname' => 'Alonso', 'nationality' => 'Spanish', 'status' => true],
            ['name' => 'Alexander', 'surname' => 'Albon', 'nationality' => 'Thai', 'status' => true],
            ['name' => 'Rubens', 'surname' => 'Barrichello', 'nationality' => 'Brazilian', 'status' => true],
            ['name' => 'Zsolt', 'surname' => 'Baumgartner', 'nationality' => 'Hungarian', 'status' => false],
            ['name' => 'Jules', 'surname' => 'Bianchi', 'nationality' => 'French', 'status' => false],
            ['name' => 'Valtteri', 'surname' => 'Bottas', 'nationality' => 'Finnish', 'status' => false],
            ['name' => 'Jenson', 'surname' => 'Button', 'nationality' => 'British', 'status' => true],
            ['name' => 'Luciano', 'surname' => 'Burti', 'nationality' => 'Brazilian', 'status' => false],
            ['name' => 'David', 'surname' => 'Coulthard', 'nationality' => 'British', 'status' => false],
            ['name' => 'Karun', 'surname' => 'Chandhok', 'nationality' => 'Indian', 'status' => false],
            ['name' => 'Mike', 'surname' => 'Conway', 'nationality' => 'British', 'status' => false],
            ['name' => 'Pedro', 'surname' => 'de la Rosa', 'nationality' => 'Spanish', 'status' => false],
            ['name' => 'Anthony', 'surname' => 'Davidson', 'nationality' => 'British', 'status' => false],
            ['name' => 'Nyck', 'surname' => 'de Vries', 'nationality' => 'Dutch', 'status' => false],
            ['name' => 'Giancarlo', 'surname' => 'Fisichella', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Heinz-Harald', 'surname' => 'Frentzen', 'nationality' => 'German', 'status' => false],
            ['name' => 'Marc', 'surname' => 'Gené', 'nationality' => 'Spanish', 'status' => true],
            ['name' => 'Pierre', 'surname' => 'Gasly', 'nationality' => 'French', 'status' => true],
            ['name' => 'Zhou', 'surname' => 'Guanyu', 'nationality' => 'Chinese', 'status' => true],
            ['name' => 'Romain', 'surname' => 'Grosjean', 'nationality' => 'French', 'status' => false],
            ['name' => 'Esteban', 'surname' => 'Gutiérrez', 'nationality' => 'Mexican', 'status' => false],
            ['name' => 'Antonio', 'surname' => 'Giovinazzi', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Timo', 'surname' => 'Glock', 'nationality' => 'Austrian', 'status' => false],
            ['name' => 'Mika', 'surname' => 'Häkkinen', 'nationality' => 'Finnish', 'status' => false],
            ['name' => 'Nick', 'surname' => 'Heidfeld', 'nationality' => 'German', 'status' => false],
            ['name' => 'Johnny', 'surname' => 'Herbert', 'nationality' => 'British', 'status' => false],
            ['name' => 'Eddie', 'surname' => 'Irvine', 'nationality' => 'British', 'status' => false],
            ['name' => 'Lewis', 'surname' => 'Hamilton', 'nationality' => 'British', 'status' => true],
            ['name' => 'Nico', 'surname' => 'Hülkenberg', 'nationality' => 'German', 'status' => false],
            ['name' => 'Heikki', 'surname' => 'Kovalainen', 'nationality' => 'Finnish', 'status' => false],
            ['name' => 'Christian', 'surname' => 'Klien', 'nationality' => 'Austrian', 'status' => false],
            ['name' => 'Kevin', 'surname' => 'Magnussen', 'nationality' => 'Danish', 'status' => false],
            ['name' => 'Narain', 'surname' => 'Karthikeyan', 'nationality' => 'Indian', 'status' => false],
            ['name' => 'Robert', 'surname' => 'Kubica', 'nationality' => 'Polish', 'status' => false],
            ['name' => 'Daniil', 'surname' => 'Kvyat', 'nationality' => 'Russian', 'status' => false],
            ['name' => 'Charles', 'surname' => 'Leclerc', 'nationality' => 'Monegasque', 'status' => true],
            ['name' => 'Vitantonio', 'surname' => 'Liuzzi', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Felipe', 'surname' => 'Massa', 'nationality' => 'Brazilian', 'status' => false],
            ['name' => 'Pastor', 'surname' => 'Maldonado', 'nationality' => 'Venezuelan', 'status' => false],
            ['name' => 'Gastón', 'surname' => 'Mazzacane', 'nationality' => 'Argentine', 'status' => false],
            ['name' => 'Roberto', 'surname' => 'Merhi', 'nationality' => 'Spanish', 'status' => false],
            ['name' => 'Tiago', 'surname' => 'Monteiro', 'nationality' => 'Portuguese', 'status' => false],
            ['name' => 'Lando', 'surname' => 'Norris', 'nationality' => 'British', 'status' => true],
            ['name' => 'Kazuki', 'surname' => 'Nakajima', 'nationality' => 'Japanese', 'status' => false],
            ['name' => 'Esteban', 'surname' => 'Ocon', 'nationality' => 'French', 'status' => true],
            ['name' => 'Jolyon', 'surname' => 'Palmer', 'nationality' => 'British', 'status' => false],
            ['name' => 'Sergio', 'surname' => 'Perez', 'nationality' => 'Mexican', 'status' => true],
            ['name' => 'Vitaly', 'surname' => 'Petrov', 'nationality' => 'Russian', 'status' => false],
            ['name' => 'Oscar', 'surname' => 'Piastri', 'nationality' => 'Australian', 'status' => true],
            ['name' => 'António', 'surname' => 'Pizzonia', 'nationality' => 'Brazilian', 'status' => false],
            ['name' => 'Carlos', 'surname' => 'Sainz', 'nationality' => 'Spanish', 'status' => true],
            ['name' => 'Mika', 'surname' => 'Salo', 'nationality' => 'Finnish', 'status' => false],
            ['name' => 'Takuma', 'surname' => 'Sato', 'nationality' => 'Japanese', 'status' => false],
            ['name' => 'Bruno', 'surname' => 'Senna', 'nationality' => 'Brazilian', 'status' => false],
            ['name' => 'Michael', 'surname' => 'Schumacher', 'nationality' => 'German', 'status' => false],
            ['name' => 'Mick', 'surname' => 'Schumacher', 'nationality' => 'German', 'status' => true],
            ['name' => 'Ralf', 'surname' => 'Schumacher', 'nationality' => 'German', 'status' => false],
            ['name' => 'Adrian', 'surname' => 'Sutil', 'nationality' => 'German', 'status' => false],
            ['name' => 'Lance', 'surname' => 'Stroll', 'nationality' => 'Canadian', 'status' => true],
            ['name' => 'Jarno', 'surname' => 'Trulli', 'nationality' => 'Italian', 'status' => false],
            ['name' => 'Yuki', 'surname' => 'Tsunoda', 'nationality' => 'Japanese', 'status' => true],
            ['name' => 'Kimi', 'surname' => 'Räikkönen', 'nationality' => 'Finnish', 'status' => false],
            ['name' => 'Nico', 'surname' => 'Rosberg', 'nationality' => 'German', 'status' => false],
            ['name' => 'George', 'surname' => 'Russell', 'nationality' => 'British', 'status' => true],
            ['name' => 'Stoffel', 'surname' => 'Vandoorne', 'nationality' => 'Belgian', 'status' => false],
            ['name' => 'Jos', 'surname' => 'Verstappen', 'nationality' => 'Dutch', 'status' => false],
            ['name' => 'Max', 'surname' => 'Verstappen', 'nationality' => 'Dutch', 'status' => true],
            ['name' => 'Sebastian', 'surname' => 'Vettel', 'nationality' => 'German', 'status' => false],
            ['name' => 'Jacques', 'surname' => 'Villeneuve', 'nationality' => 'Canadian', 'status' => false],
            ['name' => 'Mark', 'surname' => 'Webber', 'nationality' => 'Australian', 'status' => false],
            ['name' => 'Pascal', 'surname' => 'Wehrlein', 'nationality' => 'German', 'status' => false],
            ['name' => 'Alexander', 'surname' => 'Wurz', 'nationality' => 'Austrian', 'status' => false],
            ['name' => 'Alex', 'surname' => 'Yoong', 'nationality' => 'Malaysian', 'status' => false],
            ['name' => 'Ricardo', 'surname' => 'Zonta', 'nationality' => 'Brazilian', 'status' => false],
        ]);
    }
}
