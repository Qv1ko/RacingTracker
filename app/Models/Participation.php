<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

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

    public static function raceResult(string $raceId): Collection
    {
        $maxPoints = Participation::where('race_id', $raceId)
            ->select('points')
            ->max('points');

        $maxTeamPoints = Participation::where('race_id', $raceId)
            ->get()
            ->groupBy('team_id')
            ->map(function ($items, $teamId) {
                return [
                    'team_id'    => $teamId,
                    'avg_points' => $items->avg('points'),
                ];
            })
            ->values()
            ->max('avg_points');

        return Participation::where('race_id', $raceId)
            ->with(['driver', 'team', 'race'])
            ->get()
            ->map(function ($participation) use ($maxPoints, $maxTeamPoints) {
                $previousParticipation = Participation::where('driver_id', $participation->driver_id)
                    ->whereHas('race', function ($query) use ($participation) {
                        $query->where('date', '<', $participation->race->date);
                    })
                    ->orderByDesc(
                        Race::select('date')
                            ->whereColumn('races.id', 'participations.race_id')
                            ->limit(1)
                    )
                    ->first();

                $previousPoints = $previousParticipation ? $previousParticipation->points : self::$MU;

                $teamPoints = Participation::where('race_id', $participation->race_id)
                    ->where('team_id', $participation->team_id)
                    ->avg('points');

                $previousTeamParticipation = Participation::where('team_id', $participation->team_id)
                    ->whereHas('race', function ($query) use ($participation) {
                        $query->where('date', '<', $participation->race->date);
                    })
                    ->orderByDesc(
                        Race::select('date')
                            ->whereColumn('races.id', 'participations.race_id')
                            ->limit(1)
                    )
                    ->first();

                $previousTeamPoints = Participation::where('race_id', $previousTeamParticipation->race_id)
                    ->where('team_id', $participation->team_id)
                    ->avg('points') ?: self::$MU;

                return [
                    'sort_position' => $participation->position,
                    'position' => $participation->status,
                    'driver' => $participation->driver,
                    'points' => $participation->points,
                    'pointsDiff' => $participation->points - $previousPoints,
                    'pointsGap' => $maxPoints - $participation->points,
                    'team' => $participation->team,
                    'teamPoints' => $teamPoints,
                    'teamPointsDiff' => $teamPoints - $previousTeamPoints,
                    'teamPointsGap' => $maxTeamPoints - $teamPoints,
                ];
            })
            ->sort(function ($a, $b) {
                $aPos = $a['sort_position'];
                $bPos = $b['sort_position'];

                if (is_null($aPos) && !is_null($bPos)) return 1;
                if (!is_null($aPos) && is_null($bPos)) return -1;
                if (is_null($aPos) && is_null($bPos)) return 0;
                return $aPos <=> $bPos;
            })
            ->values()
            ->map(function ($item) {
                unset($item['sort_position']);
                return $item;
            });
    }


    public static function raceDriverStandings(string $raceId): Collection
    {
        $race = Race::findOrFail($raceId);

        $season = $race->season();
        $date = $race->date;

        $participations = Participation::whereHas('race', function ($query) use ($season, $date) {
            $query->whereYear('date', $season)
                ->where('date', '<=', $date);
        })
            ->with(['driver', 'race'])
            ->get()
            ->groupBy('driver_id')
            ->map(function ($group) {
                return $group->sortByDesc('race.date')->first();
            })
            ->sortByDesc('points');

        $maxPoints = $participations->max('points');

        return $participations->values()
            ->map(function ($participation, $index) use ($maxPoints) {
                return [
                    'posicion' => $index + 1,
                    'driver' => $participation->driver,
                    'points' => $participation->points,
                    'gap' => $participation->points - $maxPoints
                ];
            });
    }

    public static function raceTeamStandings(string $raceId): Collection
    {
        $race = Race::findOrFail($raceId);

        $season = $race->season();
        $date = $race->date;

        $participations = Participation::whereHas('race', function ($query) use ($season, $date) {
            $query->whereYear('date', $season)
                ->where('date', '<=', $date);
        })
            ->whereNotNull('team_id')
            ->with(['driver', 'team', 'race'])
            ->get();

        $teams = $participations
            ->groupBy('team_id')
            ->map(function ($teamGroup) {
                $lastParticipations = $teamGroup
                    ->sortByDesc('race.date')
                    ->unique('driver_id');

                $pointsAvg = $lastParticipations->avg('points');

                return [
                    'team' => $teamGroup->first()->team,
                    'points' => $pointsAvg,
                ];
            })
            ->sortByDesc('points')
            ->values();

        $maxPoints = $teams->max('points');

        return $teams->map(function ($team, $index) use ($maxPoints) {
            return [
                'posicion' => $index + 1,
                'team' => $team['team'],
                'points' => $team['points'],
                'gap' => $team['points'] - $maxPoints,
            ];
        });
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

    public static function seasonDriversClasification(string $season): Collection
    {
        $drivers = Driver::whereHas('participations.race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->get();

        return $drivers->map(function ($driver) use ($season) {
            $lastParticipationBeforeSeason = $driver->participations()
                ->with(['race' => fn($q) => $q->whereYear('date', '<', $season)])
                ->whereHas('race', fn($q) => $q->whereYear('date', '<', $season))
                ->orderByDesc(
                    Race::select('date')
                        ->whereColumn('races.id', 'participations.race_id')
                        ->orderByDesc('date')
                        ->limit(1)
                )
                ->first();

            $previousSeason = $lastParticipationBeforeSeason ? Carbon::parse($lastParticipationBeforeSeason?->race?->date)->format('Y') : null;

            $points = (float)($driver->lastPoints($season) ?? self::$MU);
            $startingPoints = (float)($previousSeason ? ($driver->lastPoints($previousSeason) ?? self::$MU) : self::$MU);

            return [
                'driver' => $driver,
                'pointsDiff' => $points - $startingPoints,
                'points' => $points,
                'startingPoints' => $startingPoints
            ];
        })->sortByDesc('pointsDiff')
            ->values()
            ->map(function ($item, $index) {
                return [
                    'position' => $index + 1,
                    'driver' => $item['driver'],
                    'pointsDiff' => $item['pointsDiff'],
                    'points' => $item['points'],
                    'startingPoints' => $item['startingPoints']
                ];
            });
    }

    public static function seasonTeamsClasification(string $season): Collection
    {
        $teams = Team::whereHas('participations.race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })->with(['participations.race', 'participations.driver'])->get();

        return $teams->map(function ($team) use ($season) {
            $seasonParticipations = $team->participations
                ->filter(function ($p) use ($season) {
                    return $p->race && $p->race->date && $p->race->season() == $season;
                });

            $latestDriverParticipations = $seasonParticipations
                ->sortByDesc(function ($p) {
                    return $p->race->date;
                })
                ->unique('driver_id');

            $points = $latestDriverParticipations->avg('points') ?? self::$MU;

            $lastParticipationBeforeSeason = $team->participations()
                ->with(['race' => fn($q) => $q->whereYear('date', '<', $season)])
                ->whereHas('race', fn($q) => $q->whereYear('date', '<', $season))
                ->orderByDesc(
                    Race::select('date')
                        ->whereColumn('races.id', 'participations.race_id')
                        ->orderByDesc('date')
                        ->limit(1)
                )
                ->first();

            $previousSeason = $lastParticipationBeforeSeason
                ? $lastParticipationBeforeSeason?->race?->season()
                : null;

            if ($previousSeason) {
                $prevSeasonParticipations = $team->participations
                    ->filter(function ($p) use ($previousSeason) {
                        return $p->race && $p->race->date && $p->race->season() == $previousSeason;
                    })
                    ->sortByDesc(function ($p) {
                        return $p->race->date;
                    })
                    ->unique('driver_id');
                $startingPoints = $prevSeasonParticipations->avg('points') ?? self::$MU;
            } else {
                $startingPoints = self::$MU;
            }

            return [
                'team' => $team,
                'points' => $points,
                'pointsDiff' => $points - $startingPoints,
                'startingPoints' => $startingPoints,
            ];
        })
            ->sortByDesc('pointsDiff')
            ->values()
            ->map(function ($item, $index) {
                return [
                    'position' => $index + 1,
                    'team' => $item['team'],
                    'pointsDiff' => $item['pointsDiff'],
                    'points' => $item['points'],
                    'startingPoints' => $item['startingPoints'],
                ];
            });

        $teams = Team::whereHas('participations.race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->get();

        return $teams->map(function ($team) use ($season) {
            $lastParticipationBeforeSeason = $team->participations()
                ->with(['race' => fn($q) => $q->whereYear('date', '<', $season)])
                ->whereHas('race', fn($q) => $q->whereYear('date', '<', $season))
                ->orderByDesc(
                    Race::select('date')
                        ->whereColumn('races.id', 'participations.race_id')
                        ->orderByDesc('date')
                        ->limit(1)
                )
                ->first();

            $previousSeason = $lastParticipationBeforeSeason ? Carbon::parse($lastParticipationBeforeSeason?->race?->date)->format('Y') : null;

            $points = (float)($team->lastPoints($season) ?? self::$MU);
            $startingPoints = (float)($previousSeason ? ($team->lastPoints($previousSeason) ?? self::$MU) : self::$MU);

            return [
                'team' => $team,
                'pointsDiff' => $points - $startingPoints,
                'points' => $points,
                'startingPoints' => $startingPoints
            ];
        })->sortByDesc('pointsDiff')
            ->values()
            ->map(function ($item, $index) {
                return [
                    'position' => $index + 1,
                    'team' => $item['team'],
                    'pointsDiff' => $item['pointsDiff'],
                    'points' => $item['points'],
                    'startingPoints' => $item['startingPoints']
                ];
            });
    }

    public static function seasonDrivers(string $season): int
    {
        return Driver::whereHas('participations.race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->count();
    }

    public static function seasonRaces(string $season): int
    {
        return Race::whereHas('participations.race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->count();
    }

    public static function seasonTeams(string $season): int
    {
        return Team::whereHas('participations.race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->count();
    }
}
