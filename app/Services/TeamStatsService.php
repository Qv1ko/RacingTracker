<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\SyncRaceParticipations;
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
        $perRace = $this->team
            ->participations()
            ->with('race')
            ->whereHas('race', fn ($q) => $q->when($season, fn ($w) => $w->whereYear('date', $season)))
            ->get()
            ->sortBy(fn ($p) => $p->race->date)
            ->groupBy(fn ($p) => $p->race->id)
            ->map(function ($rows) {
                $race = $rows->first()->race;
                $latestByDriver = [];

                foreach ($rows as $p) {
                    $latestByDriver[$p->driver_id] = (float) $p->points;
                }

                return [
                    'race_id' => $race->id,
                    'race' => $race->name,
                    'date' => $race->date,
                    'season' => $race->season,
                    'average' => array_sum($latestByDriver) / count($latestByDriver),
                ];
            })
            ->values();

        if ($season !== null) {
            return $perRace
                ->map(fn ($row) => [
                    'race_id' => $row['race_id'],
                    'race' => $row['race'],
                    'date' => $row['date'],
                    'points' => round($row['average'], 3),
                ]);
        }

        $neutral = (float) SyncRaceParticipations::neutralRating()['points'];
        $total = 0.0;
        $previousBySeason = [];

        return $perRace
            ->map(function ($row) use ($neutral, &$total, &$previousBySeason) {
                $base = $previousBySeason[$row['season']] ?? $neutral;

                $total += $row['average'] - $base;
                $previousBySeason[$row['season']] = $row['average'];

                return [
                    'race_id' => $row['race_id'],
                    'race' => $row['race'],
                    'date' => $row['date'],
                    'points' => round($total, 3),
                ];
            });
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

    private function latestPointsPerDriver(Collection $participations): Collection
    {
        return $participations
            ->groupBy('driver_id')
            ->map(fn ($group) => $group->sortByDesc('race.date')->first());
    }
}
