<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\SyncF1Data;
use App\Actions\SyncRaceParticipations;
use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;
use Illuminate\Database\Seeder;

class F1SeasonSeeder extends Seeder
{
    public function run(array $seasons = []): void
    {
        $seasons = $seasons ?: range(1950, (int) date('Y'));

        $this->command->info('Seeding F1 seasons: '.$seasons[0].' - '.end($seasons).' ('.count($seasons).' seasons)');

        $this->wipeDomainTables();

        $affectedSeasons = app(SyncF1Data::class)->syncSeasons($seasons);

        if ($affectedSeasons !== []) {
            $this->command->info('Recalculating ratings for seeded seasons...');

            app(SyncRaceParticipations::class)->recalculateSeasons($affectedSeasons);
        }

        $this->command->info('Seeding finished.');
    }

    private function wipeDomainTables(): void
    {
        foreach ([Participation::class, Race::class, Driver::class, Team::class] as $model) {
            $model::query()->delete();
        }
    }
}
