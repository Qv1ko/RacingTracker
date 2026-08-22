<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeamStatsService
{
    public function __construct(private Team $team) {}

    public function seasons(): Collection
    {
        return $this->team
            ->races()
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y'))
            ->unique()
            ->sort()
            ->values();
    }

    public function championships(): Collection
    {
        $champions = (new RankingService)->championsBySeason();

        return collect($champions['team'])
            ->filter(fn ($championTeamId) => $championTeamId === $this->team->id)
            ->keys()
            ->values();
    }

    public function getPositionsCount(?string $season = null, int $position = 1): int
    {
        return $this->team
            ->participations()
            ->whereHas('race', fn ($q) => $q->when($season, fn ($w) => $w->whereYear('date', $season)))
            ->where('position', $position)
            ->count();
    }

    public function getPodiums(?string $season = null): Collection
    {
        return $this->team
            ->participations()
            ->with('race')
            ->where('position', '<=', 3)
            ->whereHas('race', fn ($q) => $q->when($season, fn ($w) => $w->whereYear('date', $season)))
            ->get()
            ->sortBy(fn ($participation) => $participation->race->date)
            ->values();
    }

    public function pointsHistory(?string $season = null): Collection
    {
        $participations = $this->team
            ->participations()
            ->with('race')
            ->whereHas('race', fn ($q) => $q->when($season, fn ($w) => $w->whereYear('date', $season)))
            ->get()
            ->sortBy(fn ($p) => $p->race->date)
            ->values();

        return $participations
            ->groupBy(fn ($p) => $p->race->season)
            ->sortKeys()
            ->flatMap(fn ($rows) => $this->pointsHistoryForSeason($rows))
            ->values();
    }

    public function lastPoints(?string $season = null): ?float
    {
        $participations = $this->team
            ->participations()
            ->with('race')
            ->get();

        $targetSeason = ! $season
        ? $participations->pluck('race')->map(fn ($r) => $r->season)->unique()->sortDesc()->first()
        : $season;

        if ( ! $targetSeason) {
            return null;
        }

        $points = $this->latestPointsPerDriver(
            $participations->filter(fn ($p) => $p->race->season === $targetSeason)
        )->avg('points');

        return $points === null ? null : (float) number_format((float) $points, 3);
    }

    private function pointsHistoryForSeason(Collection $participations): Collection
    {
        // Chronologically sorted participations: sweep race by race keeping
        // each driver's latest points, averaging across drivers after every
        // race. Equivalent to the previous O(races x participations) filter
        // but linear.
        $latestByDriver = [];
        $history = [];

        foreach ($participations->groupBy(fn ($p) => $p->race->id) as $rows) {
            $race = $rows->first()->race;

            foreach ($rows as $p) {
                $latestByDriver[$p->driver_id] = (float) $p->points;
            }

            $history[] = [
                'race_id' => $race->id,
                'race' => $race->name,
                'date' => $race->date,
                'points' => array_sum($latestByDriver) / count($latestByDriver),
            ];
        }

        return collect($history);
    }

    private function latestPointsPerDriver(Collection $participations): Collection
    {
        return $participations
            ->groupBy('driver_id')
            ->map(fn ($group) => $group->sortByDesc('race.date')->first());
    }
}
