<?php

declare(strict_types=1);

namespace App\Listeners\Calculations;

use App\Contracts\RatingCalculation;
use App\Events\RaceResultCalculated;
use App\Models\Participation;
use Illuminate\Support\Facades\DB;

class TrueSkillCalculation implements RatingCalculation
{
    public function handle(RaceResultCalculated $event): void
    {
        foreach ($event->participations as $participation) {
            [$points, $uncertainty] = $this->previousRating($participation);

            $finishedCount = $event->participations->where('position', '>', 0)->count();
            $position = $participation->position ?? $finishedCount + 1;
            $participantCount = $event->participations->count();
            $relativeAvg = $this->relativeAverage($participation);

            $newRating = $this->updateRating($points, $uncertainty, $position, $participantCount, $relativeAvg);

            $participation->points = $newRating['mu'];
            $participation->uncertainty = $newRating['sigma'];
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
            $latest->points ?? config('ranking.defaults.mu'),
            $latest->uncertainty ?? config('ranking.defaults.sigma'),
        ];
    }

    private function relativeAverage(Participation $participation): float
    {
        $avgPosition = Participation::query()
            ->where('driver_id', $participation->driver_id)
            ->whereHas(
                'race',
                fn ($q) => $q->where('date', '<', $participation->race->date)
            )
            ->avg('position') ?? 0;

        $avgParticipants = Participation::query()
            ->whereIn('race_id', function ($q) use ($participation) {
                $q->select('race_id')
                    ->from('participations')
                    ->where('driver_id', $participation->driver_id);
            })
            ->whereHas(
                'race',
                fn ($q) => $q->where('date', '<', $participation->race->date)
            )
            ->select('race_id', DB::raw('COUNT(*) as participants'))
            ->groupBy('race_id')
            ->get()
            ->avg('participants') ?? 0;

        return ($avgPosition > 0 && $avgParticipants > 0) ? $avgPosition / $avgParticipants : 0;
    }

    private function updateRating(float $mu, float $sigma, float $position, int $participantsCount, float $avg = 0): array
    {
        $beta = $mu / 6.0;
        $tau = $mu / 300.0;
        $C = $sigma ** 2 + $beta ** 2;
        if ($C == 0.0) {
            return ['mu' => $mu, 'sigma' => $sigma];
        }

        $K = ($sigma ** 2) / $C;
        $expectedPosition = $avg * $participantsCount;
        $error = $expectedPosition - $position;
        $newMu = $mu + $K * $error;

        $errorImpact = ($error == 0 || $participantsCount == 0)
            ? 0
            : abs($error) / $participantsCount;

        $sigmaChange = $tau * (0.5 - $errorImpact);
        $maxChange = $sigma * 0.15;
        $sigmaChange = $sigmaChange > 0
            ? min($sigmaChange, $maxChange)
            : max($sigmaChange, -$maxChange);

        return [
            'mu' => $newMu,
            'sigma' => max(0.001, $sigma + $sigmaChange),
        ];
    }
}
