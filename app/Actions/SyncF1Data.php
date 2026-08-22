<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;
use App\Services\JolpicaService;
use Illuminate\Support\Facades\DB;

class SyncF1Data
{
    public function __construct(private JolpicaService $jolpica) {}

    /** @return array<int, string> Seasons that received new races or participations */
    public function syncSeasons(iterable $seasons): array
    {
        $affectedSeasons = [];

        foreach ($seasons as $season) {
            if ($this->syncSeason((int) $season)) {
                $affectedSeasons[] = (string) $season;
            }
        }

        return $affectedSeasons;
    }

    private function syncSeason(int $season): bool
    {
        $races = $this->jolpica->seasonRaces($season);

        if ($races->isEmpty()) {
            return false;
        }

        $touched = false;

        DB::transaction(function () use ($races, &$touched) {
            foreach ($races as $raceData) {
                $race = Race::firstOrCreate([
                    'name' => str_replace('_', ' ', $raceData->raceName),
                    'date' => $raceData->date,
                ]);

                if ($race->wasRecentlyCreated) {
                    $touched = true;
                }

                foreach ($raceData->Results ?? [] as $result) {
                    $driver = Driver::firstOrCreate([
                        'name' => $result->Driver->givenName,
                        'surname' => $result->Driver->familyName,
                    ], [
                        'nationality' => $result->Driver->nationality ?? null,
                        'status' => true,
                    ]);

                    $team = null;

                    if ($result->Constructor !== null) {
                        $team = Team::firstOrCreate([
                            'name' => str_replace('_', ' ', $result->Constructor->name),
                        ], [
                            'nationality' => $result->Constructor->nationality ?? null,
                            'status' => true,
                        ]);
                    }

                    [$position, $status] = $this->jolpica->resolvePositionAndStatus($result);

                    $exists = Participation::where('driver_id', $driver->id)
                        ->where('race_id', $race->id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    Participation::create([
                        'driver_id' => $driver->id,
                        'team_id' => $team?->id,
                        'race_id' => $race->id,
                        'position' => $position,
                        'status' => $status,
                        ...SyncRaceParticipations::neutralRating(),
                    ]);

                    $touched = true;
                }
            }
        });

        return $touched;
    }
}
