<?php

namespace App\Listeners\Calculations;

use App\Contracts\RatingCalculation;
use App\Events\RaceResultCalculated;
use App\Models\Participation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class TrueSkillCalculation implements RatingCalculation, ShouldQueue
{
    public string $queue = 'calculations';

    private const MU = 25.0;

    private const SIGMA = 8.333; // $MU / 3

    public function handle(RaceResultCalculated $event): void
    {
        foreach ($event->participations as $participation) {
            [$points, $uncertainty] = $this->previousRating($participation);

            $finishedCount = $event->participations->where('race_id', $participation->race_id)->where('position', '>', 0)->count();
            $position = $participation->position ?? $finishedCount + 1;
            $participantCount = $event->participations->where('race_id', $participation->race_id)->count();
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
            ->select('points', 'uncertainty')
            ->with('races')
            ->where('driver_id', $participation->driver_id)
            ->whereHas(
                'race',
                fn ($q) => $q->where('date', '<', $participation->race->date)
            )
            ->orderByDesc('races.date')
            ->latest();

        return [
            $latest->points ?? self::MU,
            $latest->uncertainty ?? self::SIGMA,
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
        $beta = $mu / 6.0; // Performance deviation
        $tau = $mu / 300.0; // Dynamic factor

        $C = $sigma ** 2 + $beta ** 2; // Performance variance
        $K = ($sigma ** 2) / $C; // Update factor

        $expectedPosition = $avg * $participantsCount;
        $error = $expectedPosition - $position; // Difference between expected and actual
        $newMu = $mu + $K * $error;

        // Adjust sigma depending on how surprising the result was
        $errorImpact = ($error == 0 || $participantsCount == 0)
            ? 0
            : abs($error) / $participantsCount;

        // More error -> increase sigma; Less error -> decrease sigma
        $sigmaChange = $tau * (0.5 - $errorImpact);
        $maxChange = $sigma * 0.15;
        $sigmaChange = $sigmaChange > 0
            ? min($sigmaChange, $maxChange)
            : max($sigmaChange, -$maxChange);

        return [
            'mu' => $newMu,
            'sigma' => max(0.001, $sigma + $sigmaChange), // Prevent sigma from reaching zero
        ];
    }
}
