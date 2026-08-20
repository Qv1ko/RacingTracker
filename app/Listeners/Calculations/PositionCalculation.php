<?php

namespace App\Listeners\Calculations;

use App\Contracts\RatingCalculation;
use App\Events\RaceResultCalculated;
use App\Models\Participation;

class PositionCalculation implements RatingCalculation
{
    public function handle(RaceResultCalculated $event): void
    {
        foreach ($event->participations as $participation) {
            [$points] = $this->previousRating($participation);

            $finishedCount = $event->participations->where('position', '>', 0)->count();

            $racePoints = $participation->position
                ? max(0, $finishedCount - $participation->position + 1)
                : 0;

            $participation->points = $points + $racePoints;
            $participation->uncertainty = 0;
            $participation->save();
        }
    }

    private function previousRating(Participation $participation): array
    {
        $latest = Participation::query()
            ->select('participations.points', 'participations.uncertainty')
            ->where('driver_id', $participation->driver_id)
            ->whereHas('race', fn ($q) => $q->where('date', '<', $participation->race->date))
            ->join('races', 'races.id', '=', 'participations.race_id')
            ->orderByDesc('races.date')
            ->first();

        return [
            $latest->points ?? 0,
            $latest->uncertainty ?? 0,
        ];
    }
}
