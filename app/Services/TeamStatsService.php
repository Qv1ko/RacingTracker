<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Participation;
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
        $participations = Participation::with('race')
            ->whereHas('race', fn ($q) => $q->when($season, fn ($w) => $w->whereYear('date', $season)))
            ->whereNotNull('team_id')
            ->get()
            ->sortBy(fn ($p) => $p->race->date);

        $races = $participations
            ->groupBy(fn ($p) => $p->race->id)
            ->sortBy(fn ($group) => $group->first()->race->date);

        $previousByDriver = [];
        $teamRunning = [];

        $result = [];

        foreach ($races as $raceId => $rows) {
            $race = $rows->first()->race;

            foreach ($rows as $row) {
                $delta = ($previousByDriver[$row->driver_id] ?? null) === null
                    ? (float) $row->points
                    : (float) $row->points - $previousByDriver[$row->driver_id];

                $previousByDriver[$row->driver_id] = (float) $row->points;

                $teamRunning[$row->team_id] = ($teamRunning[$row->team_id] ?? 0) + $delta;
            }

            $result[] = [
                'race_id' => $raceId,
                'race' => $race->name,
                'date' => $race->date,
                'points' => round($teamRunning[$this->team->id] ?? 0, 3),
            ];
        }

        return collect($result);
    }

    public function lastPoints(?string $season = null): ?float
    {
        $teamParticipations = $this->team->participations()->with('race')->get();

        if ($teamParticipations->isEmpty()) {
            return null;
        }

        $targetSeason = $season
            ?? $teamParticipations->pluck('race.season')->filter()->unique()->sortDesc()->first();

        if ($targetSeason === null) {
            return null;
        }

        $seasonParticipations = Participation::with('race')
            ->whereHas('race', fn ($q) => $q->whereYear('date', $targetSeason))
            ->whereNotNull('team_id')
            ->get();

        if ($seasonParticipations->isEmpty()) {
            return null;
        }

        $points = (new RankingService)->teamPointsFromParticipations($seasonParticipations);

        return $points->has($this->team->id)
            ? (float) $points->get($this->team->id)
            : null;
    }
}
