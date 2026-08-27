<?php

declare(strict_types=1);

namespace App\Listeners\Calculations;

use App\Contracts\RatingCalculation;
use App\Events\RaceResultCalculated;
use App\Models\Participation;

class ClassicCalculation implements RatingCalculation
{
    public function handle(RaceResultCalculated $event): void
    {
        foreach ($event->participations as $participation) {
            [$points] = $this->previousRating($participation);

            $racePoints = $participation->position
                ? config('ranking.classic_points')[$participation->position] ?? 0
                : 0;

            $participation->points = $points + $racePoints;
            $participation->uncertainty = 0;
            $participation->save();
        }

        $event->participations
            ->groupBy('driver_id')
            ->each(function ($driverRows) {
                $maxPoints = $driverRows->max('points');

                $driverRows->each(function ($participation) use ($maxPoints) {
                    if ($participation->points < $maxPoints) {
                        $participation->points = $maxPoints;
                        $participation->save();
                    }
                });
            });
    }

    private function previousRating(Participation $participation): array
    {
        $latest = Participation::query()
            ->select('participations.points', 'participations.uncertainty')
            ->where('driver_id', $participation->driver_id)
            ->whereHas('race', fn ($q) => $q
                ->whereYear('date', $participation->race->season)
                ->where('date', '<', $participation->race->date))
            ->join('races', 'races.id', '=', 'participations.race_id')
            ->orderByDesc('races.date')
            ->first();

        return [
            $latest->points ?? 0,
            $latest->uncertainty ?? 0,
        ];
    }
}
