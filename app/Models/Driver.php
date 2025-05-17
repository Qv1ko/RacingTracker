<?php

namespace App\Models;

use App\Models\Participation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Driver extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'name',
        'surname',
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
            return Participation::seasonDriversClasification($season)
                ->contains(function ($item) {
                    return $item['position'] === 1 && $item['driver']->id === $this->id;
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

    public function teams(string $season = 'all'): Collection
    {
        return $this->participations()
            ->when($season !== 'all', function ($query) use ($season) {
                $query->whereHas('race', fn($q) => $q->whereYear('date', $season));
            })
            ->get()
            ->sortByDesc(function ($participation) {
                return $participation->race->date;
            })
            ->pluck('team')
            ->unique('id')
            ->map(function ($team) {
                if (!$team) return null;

                return (object) [
                    'id' => $team->id,
                    'name' => $team->name,
                    'nationality' => $team->nationality
                ];
            })
            ->values();
    }

    public function teammates(string $season = 'all'): Collection
    {
        return Participation::with(['driver:id,name,surname,nationality', 'race:id,date'])
            ->select('participations.driver_id', 'participations.race_id', 'participations.team_id')
            ->whereIn(
                'race_id',
                Participation::where('driver_id', $this->id)
                    ->when($season !== 'all', function ($q) use ($season) {
                        $q->whereHas('race', fn($r) => $r->whereYear('date', $season));
                    })
                    ->select('race_id')
            )
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('participations as p2')
                    ->whereColumn('p2.race_id', 'participations.race_id')
                    ->where('p2.driver_id', $this->id)
                    ->whereColumn('p2.team_id', 'participations.team_id');
            })
            ->where('driver_id', '!=', $this->id)
            ->get()
            ->groupBy('driver_id')
            ->map(function ($group) {
                $lastRace = $group->sortByDesc('race.date')->first();
                return (object)[
                    'id' => $lastRace->driver->id,
                    'name' => $lastRace->driver->name,
                    'surname' => $lastRace->driver->surname,
                    'nationality' => $lastRace->driver->nationality,
                    'last_shared_race_date' => $lastRace->race->date
                ];
            })
            ->sortByDesc('last_shared_race_date')
            ->values()
            ->map(function ($item) {
                unset($item->last_shared_race_date);
                return $item;
            });
    }

    public function races(string $season = 'all'): Collection
    {
        return Race::join('participations', 'participations.race_id', '=', 'races.id')
            ->where('participations.driver_id', $this->id)
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->orderBy('races.date', 'asc')
            ->distinct('races.id')
            ->get(['races.*']);
    }

    public function activity(string $season = 'all'): Collection
    {
        return $this->participations()
            ->toBase()
            ->select('participations.status', 'participations.race_id', 'races.name', 'races.date')
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->orderBy('races.date', 'asc')
            ->get();
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
            ->orderBy('races.date', 'asc')
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
            ->orderBy('races.date', 'asc')
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
            ->orderBy('races.date', 'asc')
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
        return $this->participations()
            ->toBase()
            ->select('participations.points', 'races.name as race', 'races.date as date')
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->orderBy('races.date', 'asc')
            ->get();
    }

    public function lastPoints(string $season = 'all'): float | null
    {
        $points = $this->participations()
            ->toBase()
            ->select('participations.points')
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->orderBy('races.date', 'desc')
            ->limit(1)
            ->value('points') ?? null;

        return $points !== null ? number_format((float)$points, 3) : null;
    }
}
