<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\RaceResultCalculated;
use App\Models\Participation;
use App\Models\Race;
use Illuminate\Support\Facades\DB;

class SyncRaceParticipations
{
    public static function neutralRating(): array
    {
        return config('ranking.algorithm') === 'trueskill'
            ? [
                'points' => config('ranking.defaults.mu'),
                'uncertainty' => config('ranking.defaults.sigma'),
            ]
            : ['points' => 0, 'uncertainty' => 0];
    }

    public function handle(Race $race, array $result, ?string $recalculateFrom = null): void
    {
        DB::transaction(function () use ($race, $result, $recalculateFrom) {
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

            $this->recalculateFrom($recalculateFrom ?? $race->date);
        });
    }

    private function previousRating(int|string $driverId, Race $race): array
    {
        $latest = Participation::where('driver_id', $driverId)
            ->whereHas('race', fn ($query) => $query
                ->whereYear('date', $race->season)
                ->where('date', '<', $race->date))
            ->orderByDesc(Race::select('date')
                ->whereColumn('races.id', 'participations.race_id')
                ->limit(1))
            ->first();

        $neutral = self::neutralRating();

        return [
            $latest !== null ? $latest->points : $neutral['points'],
            $latest !== null ? $latest->uncertainty : $neutral['uncertainty'],
        ];
    }

    public function recalculateFrom(string $date): void
    {
        $query = Participation::whereHas('race', fn ($query) => $query->where('date', '>=', $date));

        $query->update(self::neutralRating());

        $this->replayRaces($query);
    }

    public function recalculateSeasons(iterable $seasons): void
    {
        foreach ($seasons as $season) {
            $query = Participation::whereHas('race', fn ($q) => $q->whereYear('date', $season));

            $query->update(self::neutralRating());

            $this->replayRaces($query);
        }
    }

    public function resetToNeutral(iterable $races): void
    {
        $raceIds = collect($races)->map(fn (Race $race) => $race->id)->all();

        if ($raceIds === []) {
            return;
        }

        Participation::whereIn('race_id', $raceIds)->update(self::neutralRating());
    }

    public function replayRace(Race $race): void
    {
        RaceResultCalculated::dispatch(
            $race->participations()->with('race')->get()
        );
    }

    private function replayRaces($query): void
    {
        $races = Race::whereIn('id', (clone $query)->select('race_id'))
            ->orderBy('date')
            ->get();

        foreach ($races as $race) {
            $this->replayRace($race);
        }
    }
}
