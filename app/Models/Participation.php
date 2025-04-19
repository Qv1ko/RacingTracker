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

    public static function clacRaceResult($results): void
    {
        $finishedParticipations = 0;

        foreach ($results as $participation) {
            if ($participation->position) {
                $finishedParticipations++;
            }
        }

        foreach ($results as $participation) {
            $position = $participation->position ? $participation->position : $finishedParticipations + 1;

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
                $participation->points,
                $participation->uncertainty,
                $position,
                count($results),
                $avgPosition === 0 || $avgRaceParticipants === 0 ? 0 : $avgPosition / $avgRaceParticipants
            );

            $participation->points = $newRating['mu'];
            $participation->uncertainty = $newRating['sigma'];

            $participation->save();
        }
    }

    private static function updateRating(float $mu, float $sigma, float $position, int $participantsCount, float $avg = 0): array
    {
        // Performance deviation
        $beta = self::$MU / 6.0;
        // Dynamic factor
        $tau = self::$MU / 300.0;

        // Dynamic sigma
        $sd = sqrt($sigma * $sigma + $tau * $tau);

        // Profit
        $c2 = $sd * $sd + $beta * $beta;
        $K = ($sd * $sd) / $c2;

        // Diff
        $error = $avg * $participantsCount - $position;

        $newMu  = $mu + $K * $error;
        $newSigma = sqrt(($sigma * $sigma * $beta * $beta) / $c2);

        return ['mu' => $newMu, 'sigma' => $newSigma];
    }
}
