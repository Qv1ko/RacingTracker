<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Participation extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'driver_id',
        'team_id',
        'race_id',
        'position',
        'status',
        'points',
        'uncertainty',
    ];

    public static $MU = 25.0;
    public static $SIGMA = 8.333; // $MU / 3

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public static function calcRaceResult($results): void
    {
        foreach ($results as $participation) {
            $latestParticipation = Participation::select('points', 'uncertainty')
                ->with(['race' => function ($query) use ($participation) {
                    $query->where('date', '<', $participation->race->date);
                }])
                ->where('driver_id', $participation->driver_id)
                ->whereHas('race', function ($query) use ($participation) {
                    $query->where('date', '<', $participation->race->date);
                })
                ->join('races', 'races.id', '=', 'participations.race_id')
                ->orderByDesc('races.date')
                ->first();

            $points = $latestParticipation->points ?? self::$MU;
            $uncertainty = $latestParticipation->uncertainty ?? self::$SIGMA;

            $finishedParticipations = $results->where('race_id', $participation->race_id)->where('position', '>', 0)->count();

            $position = $participation->position ? $participation->position : $finishedParticipations + 1;

            $raceParticipantsCount = $results->where('race_id', $participation->race_id)->count();

            $avgPosition = Participation::where('driver_id', $participation->driver_id)->whereHas('race', function ($query) use ($participation) {
                $query->where('date', '<', $participation->race->date);
            })->avg('position') ?? 0;

            $avgRaceParticipants = Participation::whereIn('race_id', function ($query) use ($participation) {
                $query->select('race_id')
                    ->from('participations')
                    ->where('driver_id', $participation->driver_id);
            })
                ->whereHas('race', function ($query) use ($participation) {
                    $query->where('date', '<', $participation->race->date);
                })
                ->select('race_id', DB::raw('COUNT(*) as participants'))
                ->groupBy('race_id')
                ->get()
                ->avg('participants') ?? 0;

            $newRating = self::updateRating(
                $points,
                $uncertainty,
                $position,
                $raceParticipantsCount,
                $avgPosition === 0 || $avgRaceParticipants === 0 ? 0 : $avgPosition / $avgRaceParticipants
            );

            $participation->points = $newRating['mu'];
            $participation->uncertainty = $newRating['sigma'];

            $participation->save();
        }
    }

    private static function updateRating(float $mu, float $sigma, float $position, int $participantsCount, float $avg = 0): array
    {
        $beta = $mu / 6.0; // Performance deviation
        $tau = $mu / 300.0; // Dynamic factor

        $C = $sigma * $sigma + $beta * $beta; // Performance variance
        $K = ($sigma * $sigma) / $C; // Update factor

        $expectedPosition = $avg * $participantsCount;

        // Difference between expected and actual
        $error = $expectedPosition - $position;

        $newMu  = $mu + $K * $error;

        // Adjust sigma depending on how surprising the result was
        $errorImpact = $error == 0 || $participantsCount == 0 ? 0 : abs($error) / $participantsCount;

        // More error -> increase sigma; Less error -> decrease sigma
        $sigmaChange = $tau * (0.5 - $errorImpact);
        $maxChange = $sigma * 0.15;

        if ($sigmaChange > 0) {
            $sigmaChange = min($sigmaChange, $maxChange);
        } else {
            $sigmaChange = max($sigmaChange, -$maxChange);
        }

        $newSigma = max(0.001, ($sigma + $sigmaChange)); // Prevent sigma from reaching zero

        return ['mu' => $newMu, 'sigma' => $newSigma];
    }
}
