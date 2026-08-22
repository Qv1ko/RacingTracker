<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\SyncRaceParticipations;
use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class F1SeasonSeeder extends Seeder
{
    private const API_URL = 'https://api.jolpi.ca/ergast/f1';

    private const PAGE_SIZE = 100;

    private const REQUEST_DELAY_MS = 300;

    private const STATUS_MAP = [
        'R' => 'DNF',
        'W' => 'DNS',
        'D' => 'DQ',
        'E' => 'EXC',
        'F' => 'DNQ',
        'N' => 'NC',
    ];

    public function run(array $seasons = []): void
    {
        $seasons = $seasons ?: range(1950, (int) date('Y') - 1);

        $this->command->info('Seeding F1 seasons: '.$seasons[0].' - '.end($seasons).' ('.count($seasons).' seasons)');

        $this->wipeDomainTables();

        $firstRaceDate = null;

        foreach ($seasons as $season) {
            $firstRaceDateForSeason = $this->seedSeason((int) $season);
            $firstRaceDate ??= $firstRaceDateForSeason;
        }

        if ($firstRaceDate !== null) {
            (new SyncRaceParticipations)->recalculateFrom($firstRaceDate);
            $this->command->info('Ratings recalculated from '.$firstRaceDate.'.');
        }
    }

    private function wipeDomainTables(): void
    {
        foreach ([Participation::class, Race::class, Driver::class, Team::class] as $model) {
            $model::query()->delete();
        }
    }

    private function seedSeason(int $season): ?string
    {
        $races = $this->fetchSeasonRaces($season);

        if ($races->isEmpty()) {
            $this->command->warn("Season {$season} has no races, skipping.");

            return null;
        }

        $firstRaceDate = null;

        DB::transaction(function () use ($races, &$firstRaceDate) {
            foreach ($races as $raceData) {
                $race = Race::firstOrCreate([
                    'name' => str_replace('_', ' ', $raceData->raceName),
                    'date' => $raceData->date,
                ]);

                $firstRaceDate ??= $race->date;

                foreach ($raceData->Results ?? [] as $result) {
                    $driver = $this->upsertDriver($result->Driver);
                    $team = $result->Constructor !== null ? $this->upsertTeam($result->Constructor) : null;

                    [$position, $status] = $this->resolvePositionAndStatus($result);

                    Participation::firstOrCreate([
                        'driver_id' => $driver->id,
                        'race_id' => $race->id,
                        'team_id' => $team?->id,
                    ], [
                        'position' => $position,
                        'status' => $status,
                        'points' => config('ranking.defaults.mu'),
                        'uncertainty' => config('ranking.defaults.sigma'),
                    ]);
                }
            }
        });

        $this->command->info("Season {$season}: {$races->count()} races seeded.");

        return $firstRaceDate;
    }

    private function fetchSeasonRaces(int $season): Collection
    {
        $racesByRound = [];
        $offset = 0;
        $total = null;

        do {
            $data = $this->get("{$season}/results.json?limit=".self::PAGE_SIZE."&offset={$offset}")->MRData;
            $total ??= (int) $data->total;

            foreach ($data->RaceTable->Races as $race) {
                if (isset($racesByRound[$race->round])) {
                    $racesByRound[$race->round]->Results = array_merge(
                        $racesByRound[$race->round]->Results ?? [],
                        $race->Results ?? [],
                    );
                } else {
                    $racesByRound[$race->round] = $race;
                }
            }

            $offset += self::PAGE_SIZE;

            ksort($racesByRound);
        } while ($offset < $total);

        return collect($racesByRound)->sortBy('date')->values();
    }

    private function upsertDriver(object $driverData): Driver
    {
        return Driver::firstOrCreate([
            'name' => $driverData->givenName,
            'surname' => $driverData->familyName,
        ], [
            'nationality' => $driverData->nationality ?? null,
            'status' => true,
        ]);
    }

    private function upsertTeam(object $constructorData): Team
    {
        return Team::firstOrCreate([
            'name' => str_replace('_', ' ', $constructorData->name),
        ], [
            'nationality' => $constructorData->nationality ?? null,
            'status' => true,
        ]);
    }

    private function resolvePositionAndStatus(object $result): array
    {
        if (is_numeric($result->positionText)) {
            return [(int) $result->positionText, (string) $result->positionText];
        }

        return [null, $this->resolveStatus($result)];
    }

    private function resolveStatus(object $result): string
    {
        $status = strtolower($result->status ?? '');

        $mapped = match (true) {
            str_contains($status, 'not qualified') || str_contains($status, 'did not qualify') => 'DNQ',
            str_contains($status, 'not start') || str_contains($status, 'withdrew') => 'DNS',
            str_contains($status, 'disqualif') => 'DQ',
            str_contains($status, 'exclu') => 'EXC',
            str_contains($status, 'not classified') => 'NC',
            default => self::STATUS_MAP[$result->positionText] ?? 'DNF',
        };

        return substr($mapped, 0, 10);
    }

    private function get(string $path): object
    {
        usleep(self::REQUEST_DELAY_MS * 1000);

        for ($attempt = 1; ; $attempt++) {
            try {
                $response = Http::timeout(60)->get(self::API_URL.'/'.$path);
            } catch (ConnectionException $e) {
                if ($attempt >= 6) {
                    throw $e;
                }

                sleep(15 * $attempt);

                continue;
            }

            if ($response->status() === 429 && $attempt < 6) {
                $this->command->warn('Rate limited, waiting 60 seconds...');

                sleep(60);

                continue;
            }

            if ($response->successful()) {
                return json_decode($response->body());
            }

            if ($attempt >= 4) {
                $response->throw();
            }

            sleep(10);
        }
    }
}
