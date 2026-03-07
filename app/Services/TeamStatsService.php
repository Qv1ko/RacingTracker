<?php

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
        return $this->seasons()
            ->filter(
                fn ($season) => (new RankingService)
                    ->seasonTeamsClassification($season)
                    ->contains(fn ($item) => $item['position'] === 1 && $item['team']->id === $this->team->id)
            )
            ->values();
    }

    public function getPositionsCount(?string $season = null, int $position = 1): int
    {
        return $this->team
            ->participations()
            ->whereHas('race', fn ($q) => $q->inSeason($season))
            ->where('position', $position)
            ->count();
    }

    public function getPodiums(?string $season = null): Collection
    {
        return $this->team
            ->participations()
            ->with('race')
            ->where('position', '<=', 3)
            ->whereHas('race', fn ($q) => $q->inSeason($season))
            ->get()
            ->sortBy(fn ($participation) => $participation->race->date)
            ->values();
    }

    public function pointsHistory(?string $season = null): Collection
    {
        $participations = $this->team
            ->participations()
            ->whereHas('race', fn ($q) => $q->inSeason($season))
            ->get();

        $seasons = $season ? collect([$season]) : $participations->pluck('race')->map(fn ($r) => $r->season())->unique()->sort()->values();

        return $seasons->flatMap(
            fn ($qs) => $this->pointsHistoryForSeason(
                $participations->filter(fn ($qp) => $qp->race->season() === $qs)
            )
        )
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

        if (! $targetSeason) {
            return null;
        }

        $points = $this->latestPointsPerDriver(
            $participations->filter(fn ($p) => $p->race->season === $targetSeason)
        )->avg('points');

        return $points === null ? null : (float) number_format((float) $points, 3);
    }

    private function pointsHistoryForSeason(Collection $participations): Collection
    {
        return $participations
            ->pluck('race')
            ->unique('id')
            ->sortBy('date')
            ->values()
            ->map(fn ($race) => [
                'race_id' => $race->id,
                'race' => $race->name,
                'date' => $race->date,
                'points' => $this->latestPointsPerDriver(
                    $participations->filter(fn ($qp) => $qp->race->date <= $race->date)
                )->avg('points'),
            ]);
    }

    private function latestPointsPerDriver(Collection $participations): Collection
    {
        return $participations
            ->groupBy('driver_id')
            ->map(fn ($group) => $group->sortByDesc('race.date')->first());
    }
}
