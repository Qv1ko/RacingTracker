<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    private const SEASONS = 5;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startYear = now()->year - self::SEASONS;

        for ($i = 0; $i < self::SEASONS; $i++) {
            $seasonYear = $startYear + $i;

            $this->updateDrivers(3, 20, 3);
            $this->updateTeams(1, 10, 1);

            $races = Race::factory()->fromYear($seasonYear)->count(rand(12, 24))->create();

            $driverCount = Driver::where('status', true)->count();
            $minParticipants = (int) round($driverCount * 0.9);

            foreach ($races as $race) {
                $participants = rand($minParticipants, $driverCount - 1);
                for ($j = 0; $j < $participants; $j++) {
                    Participation::factory()->race($race->id)->create();
                }
                Participation::calcRaceResult(Participation::whereHas('race', function ($query) use ($race) {
                    $query->where('date', '>=', $race->date);
                })->get());
            }
        }
    }

    private function updateDrivers(int $retireCount, int $initialCreate, int $additionalCreate): void
    {
        $drivers = Driver::where('status', true)
            ->inRandomOrder()
            ->limit($retireCount)
            ->get();

        $drivers->each->update(['status' => false]);

        $activeCount = Driver::where('status', true)->count();


        Driver::factory()->count($activeCount === 0 ? $initialCreate : $additionalCreate)->create();
    }

    private function updateTeams(int $retireCount, int $initialCreate, int $additionalCreate): void
    {
        $teams = Team::where('status', true)
            ->inRandomOrder()
            ->limit($retireCount)
            ->get();

        $teams->each->update(['status' => false]);

        $activeCount = Team::where('status', true)->count();

        Team::factory()->count($activeCount === 0 ? $initialCreate : $additionalCreate)->create();
    }
}
