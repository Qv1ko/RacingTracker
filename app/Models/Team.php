<?php

namespace App\Models;

use App\Models\Participation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Team extends Model
{
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

    public function races(string $season = 'all'): int
    {
        return $this->participations()
            ->toBase()
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->distinct('participations.race_id')
            ->count('participations.race_id');
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

    public function wins(string $season = 'all'): int
    {
        return $this->participations()
            ->toBase()
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->where('position', 1)
            ->distinct('race_id')
            ->count('race_id');
    }

    public function secondPositions(string $season = 'all'): int
    {
        return $this->participations()
            ->toBase()
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->where('position', 2)
            ->distinct('race_id')
            ->count('race_id');
    }

    public function thirdPositions(string $season = 'all'): int
    {
        return $this->participations()
            ->toBase()
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->where('position', 3)
            ->distinct('race_id')
            ->count('race_id');
    }

    public function pointsHistory(string $season = 'all'): Collection
    {
        return $this->participations()
            ->selectRaw('races.name as race, AVG(participations.points) as points')
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->groupBy('races.id')
            ->orderBy('races.date', 'asc')
            ->get();
    }

    public function lastPoints(string $season = 'all'): string | null
    {
        $lastRaceId = $this->participations()
            ->toBase()
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
            ->orderBy('races.date', 'desc')
            ->limit(1)
            ->value('races.id');

        $points = $this->participations()
            ->toBase()
            ->where('race_id', $lastRaceId)
            ->avg('points');

        return $points !== null ? number_format((float)$points, 3) : null;
    }
}
