<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\RaceResultCalculated;
use App\Models\Participation;
use App\Models\Race;
use Illuminate\Support\Facades\DB;

class SyncRaceParticipations
{
    public function handle(Race $race, array $result): void
    {
        DB::transaction(function () use ($race, $result) {
            $race->participations()->delete();

            foreach ($result as $participation) {
                [$previousPoints, $previousUncertainty] = $this->previousRating($participation['driver'], $race);

                Participation::create([
                    'driver_id' => $participation['driver'],
                    'team_id' => $participation['team'],
                    'race_id' => $race->id,
                    'position' => intval($participation['position']) ? intval($participation['position']) : null,
                    'status' => $participation['position'],
                    'points' => $previousPoints,
                    'uncertainty' => $previousUncertainty,
                ]);
            }

            $this->recalculateFrom($race->date);
        });
    }

    private function previousRating(int|string $driverId, Race $race): array
    {
        $latest = Participation::where('driver_id', $driverId)
            ->whereHas('race', fn ($query) => $query->where('date', '<', $race->date))
            ->orderByDesc(Race::select('date')
                ->whereColumn('races.id', 'participations.race_id')
                ->limit(1))
            ->first();

        return [
            $latest !== null ? $latest->points : config('ranking.defaults.mu'),
            $latest !== null ? $latest->uncertainty : config('ranking.defaults.sigma'),
        ];
    }

    public function recalculateFrom(string $date): void
    {
        // Start every recalculation from an algorithm-neutral state so
        // ratings from a previously active system never leak into the new
        // one (e.g. trueskill decimals surviving a switch to classic).
        $neutral = config('ranking.algorithm') === 'trueskill'
            ? [
                'points' => config('ranking.defaults.mu'),
                'uncertainty' => config('ranking.defaults.sigma'),
            ]
            : ['points' => 0, 'uncertainty' => 0];

        Participation::whereHas('race', fn ($query) => $query->where('date', '>=', $date))
            ->update($neutral);

        Participation::whereHas('race', fn ($query) => $query->where('date', '>=', $date))
            ->with('race')
            ->get()
            ->sortBy(fn ($participation) => $participation->race->date)
            ->groupBy('race_id')
            ->each(fn ($participations) => RaceResultCalculated::dispatch($participations));
    }
}
