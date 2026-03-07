<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Participation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DriverStatsService
{
    public function __construct(private Driver $driver) {}

    public function seasons(): Collection
    {
        return $this->driver
            ->races()
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y'))
            ->unique()
            ->sort()
            ->values();
    }

    public function championships(): Collection
    {
        return $this->seasons()->filter(function ($season) {
            return Participation::seasonDriversClassification($season)
                ->contains(fn ($item) => $item['position'] === 1 && $item['driver']->id === $this->driver->id);
        })->values();
    }

    public function lastPoints(?string $season = null): ?float
    {
        $points = $this->driver
            ->participations()
            ->toBase()
            ->select('participations.points')
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season, fn ($q) => $q->whereYear('races.date', $season))
            ->orderByDesc('races.date')
            ->limit(1)
            ->value('points');

        return $points !== null ? (float) number_format((float) $points, 3) : null;
    }

    public function pointsHistory(?string $season = null): Collection
    {
        return $this->driver
            ->participations()
            ->toBase()
            ->select('participations.points', 'races.name as race', 'races.date as date')
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season, fn ($q) => $q->whereYear('races.date', $season))
            ->orderBy('races.date', 'asc')
            ->get();
    }

    public function podiums(?string $season = null): Collection
    {
        return $this->driver
            ->participations()
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season, fn ($q) => $q->whereYear('races.date', $season))
            ->where('position', '<=', 3)
            ->orderBy('races.date', 'asc')
            ->distinct('participations.race_id')
            ->get();
    }

    public function countForPosition(?string $season = null): Collection
    {
        $results = $this->driver
            ->participations()
            ->toBase()
            ->when($season, fn ($q) => $q->whereHas('race', fn ($r) => $r->whereYear('date', $season)))
            ->selectRaw('position, count(*) as times')
            ->groupBy('position')
            ->orderBy('position', 'asc')
            ->get();

        return $results
            ->map(fn ($item) => [
                'position' => $item->position,
                'times' => $item->times,
                'position_numeric' => is_numeric($item->position) ? (int) $item->position : null,
            ])
            ->sortBy([
                fn ($a, $b) => match (true) {
                    is_null($a['position_numeric']) => 1,
                    is_null($b['position_numeric']) => -1,
                    default => $a['position_numeric'] <=> $b['position_numeric'],
                },
            ])
            ->map(fn ($item) => ['position' => $item['position'], 'times' => $item['times']])
            ->values();
    }

    public function getPositionsCount(?string $season = null, int $position = 1): int
    {
        return $this->driver
            ->participations()
            ->whereHas('race', fn ($q) => $q->inSeason($season))
            ->where('position', $position)
            ->count();
    }

    public function activity(?string $season = null): Collection
    {
        return $this->driver
            ->participations()
            ->with('race')
            ->whereHas('race', fn ($q) => $q->inSeason($season))
            ->get()
            ->sortBy(fn ($p) => $p->race->date)
            ->values();
    }

    public function teammates(?string $season = null): Collection
    {
        return Participation::with(['driver:id,name,surname,nationality', 'race:id,date'])
            ->select('participations.driver_id', 'participations.race_id', 'participations.team_id')
            ->whereIn(
                'race_id',
                Participation::where('driver_id', $this->driver->id)
                    ->when($season !== 'all', function ($q) use ($season) {
                        $q->whereHas('race', fn ($r) => $r->whereYear('date', $season));
                    })
                    ->select('race_id')
            )
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('participations as p2')
                    ->whereColumn('p2.race_id', 'participations.race_id')
                    ->where('p2.driver_id', $this->driver->id)
                    ->whereColumn('p2.team_id', 'participations.team_id');
            })
            ->where('driver_id', '!=', $this->driver->id)
            ->get()
            ->groupBy('driver_id')
            ->map(function ($group) {
                $lastRace = $group->sortByDesc('race.date')->first();

                return (object) [
                    'id' => $lastRace->driver->id,
                    'name' => $lastRace->driver->name,
                    'surname' => $lastRace->driver->surname,
                    'nationality' => $lastRace->driver->nationality,
                    'last_shared_race_date' => $lastRace->race->date,
                ];
            })
            ->sortByDesc('last_shared_race_date')
            ->values()
            ->map(function ($item) {
                unset($item->last_shared_race_date);

                return $item;
            });
    }
}
