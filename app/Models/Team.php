<?php

namespace App\Models;

use App\Models\Participation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Team extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'name',
        'nationality',
        'status'
    ];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function championships(): Collection | null
    {
        $seasons = $this->seasons()->filter(function ($season) {
            return Participation::seasonTeamsClasification($season)
                ->contains(function ($item) {
                    return $item['position'] === 1 && $item['team']->id === $this->id;
                });
        })->values();

        return $seasons->isEmpty() ? null : $seasons;
    }

    public function seasons(): Collection
    {
        return $this->participations()
            ->with('race')
            ->get()
            ->map(function ($participation) {
                return $participation->race->season();
            })
            ->unique();
    }

    public function drivers(string $season = 'all'): Collection
    {
        return $this->participations()
            ->when($season !== 'all', function ($query) use ($season) {
                $query->whereHas('race', function ($q) use ($season) {
                    $q->whereYear('date', $season);
                });
            })
            ->get()
            ->sortByDesc(function ($participation) {
                return $participation->race->date;
            })
            ->pluck('driver')
            ->unique('id')
            ->map(function ($driver) {
                if (!$driver) return null;

                return (object) [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'surname' => $driver->surname,
                    'nationality' => $driver->nationality
                ];
            })
            ->values();
    }

    public function races(string $season = 'all'): Collection
    {
        return $this->participations()
            ->toBase()
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->orderBy('races.date', 'asc')
            ->distinct('participations.race_id')
            ->get(['races.*']);
    }

    public function countForPosition(string $season = 'all'): Collection
    {
        $results = $this->participations()
            ->toBase()
            ->when($season !== 'all', function ($query) use ($season) {
                $query->whereHas('race', fn($q) => $q->whereYear('date', $season));
            })
            ->select('status as position', DB::raw('count(*) as times'))
            ->groupBy('status')
            ->orderBy('position', 'asc')
            ->get();

        return $results->map(function ($item) {
            return [
                'position' => $item->position,
                'times' => $item->times,
                'position_numeric' => is_numeric($item->position) ? (int)$item->position : null
            ];
        })->sortBy([
            fn($a, $b) => match (true) {
                is_null($a['position_numeric']) => 1,
                is_null($b['position_numeric']) => -1,
                default => $a['position_numeric'] <=> $b['position_numeric']
            }
        ])->map(function ($item) {
            return [
                'position' => $item['position'],
                'times' => $item['times']
            ];
        })
            ->values();
    }

    public function wins(string $season = 'all'): Collection
    {
        return $this->participations()
            ->toBase()
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->where('position', 1)
            ->distinct('race_id')
            ->get();
    }

    public function secondPositions(string $season = 'all'): Collection
    {
        return $this->participations()
            ->toBase()
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->where('position', 2)
            ->distinct('race_id')
            ->get();
    }

    public function thirdPositions(string $season = 'all'): Collection
    {
        return $this->participations()
            ->toBase()
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->where('position', 3)
            ->distinct('race_id')
            ->get();
    }

    public function podiums(string $season = 'all'): Collection
    {
        return $this->participations()
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->where('position', '<=', 3)
            ->orderBy('races.date', 'asc')
            ->distinct('participations.race_id')
            ->get();
    }

    public function pointsHistory(string $season = 'all'): Collection
    {
        if ($season === 'all') {
            $participations = $this->participations()->with('race')->get();
            $seasons = $participations
                ->pluck('race')
                ->map(fn($race) => $race->season())
                ->unique()
                ->sort()
                ->values();

            return $seasons->flatMap(function ($season) use ($participations) {
                $seasonParticipations = $participations->filter(function ($p) use ($season) {
                    return $p->race->season() == $season;
                });

                $races = $seasonParticipations->pluck('race')->unique('id')->sortBy('date')->values();

                return $races->map(function ($race) use ($seasonParticipations) {
                    $participationsUpToRace = $seasonParticipations->filter(function ($p) use ($race) {
                        return $p->race->date <= $race->date;
                    });

                    $latestPerDriver = $participationsUpToRace
                        ->groupBy('driver_id')
                        ->map(function ($driverGroup) {
                            return $driverGroup->sortByDesc('race.date')->first();
                        });

                    $averagePoints = $latestPerDriver->avg('points');

                    return [
                        'race_id' => $race->id,
                        'race' => $race->name,
                        'date' => $race->date,
                        'points' => $averagePoints,
                    ];
                });
            })->values();
        } else {
            $participations = $this->participations()
                ->with('race')
                ->whereHas('race', function ($q) use ($season) {
                    $q->whereYear('date', $season);
                })
                ->get();

            $races = $participations->pluck('race')->unique('id')->sortBy('date')->values();

            return $races->map(function ($race) use ($participations) {
                $participationsUpToRace = $participations->filter(function ($p) use ($race) {
                    return $p->race->date <= $race->date;
                });

                $latestPerDriver = $participationsUpToRace
                    ->groupBy('driver_id')
                    ->map(function ($driverGroup) {
                        return $driverGroup->sortByDesc('race.date')->first();
                    });

                $averagePoints = $latestPerDriver->avg('points');

                return [
                    'race_id' => $race->id,
                    'race' => $race->name,
                    'date' => $race->date,
                    'points' => $averagePoints,
                ];
            });
        }
    }

    public function lastPoints(string $season = 'all'): float | null
    {
        $participations = $this->participations()->with('race')->get();

        if ($season === 'all') {
            $latestSeason = $participations
                ->pluck('race')
                ->map(fn($race) => $race->season())
                ->unique()
                ->sortDesc()
                ->first();

            if (!$latestSeason) {
                return null;
            }

            $seasonParticipations = $participations->filter(function ($p) use ($latestSeason) {
                return $p->race->season() == $latestSeason;
            });
        } else {
            $seasonParticipations = $participations->filter(function ($p) use ($season) {
                return $p->race->season() == $season;
            });
        }

        $latestParticipations = $seasonParticipations
            ->groupBy('driver_id')
            ->map(function ($driverGroup) {
                return $driverGroup->sortByDesc('race.date')->first();
            });

        $points = $latestParticipations->avg('points');

        return $points !== null ? number_format((float)$points, 3) : null;
    }
}
