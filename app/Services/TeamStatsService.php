<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\SyncRaceParticipations;
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

    public function activity(?string $season = null): Collection
    {
        $raceIds = $this->team
            ->participations()
            ->whereHas('race', fn ($q) => $q->when($season, fn ($w) => $w->whereYear('date', $season)))
            ->pluck('race_id')
            ->unique();

        if ($raceIds->isEmpty()) {
            return collect();
        }

        $participations = Participation::with('race:id,name,date')
            ->whereIn('race_id', $raceIds)
            ->where('status', '!=', 'DQ')
            ->get()
            ->sortBy(fn ($p) => $p->race->date);

        $teamPoints = [];
        $previousByDriverSeason = [];
        $currentYear = null;
        $result = [];

        foreach ($participations->groupBy('race_id')->sortBy(fn ($rows) => $rows->first()->race->date) as $rows) {
            $race = $rows->first()->race;
            $year = substr($race->date, 0, 4);

            if ($currentYear !== $year) {
                $teamPoints = [];
                $currentYear = $year;
            }

            foreach ($rows->groupBy(fn ($p) => $p->driver_id)->map(
                fn ($driverRows) => $driverRows->sortByDesc('points')->first(),
            ) as $participation) {
                $base = $previousByDriverSeason[$participation->driver_id][$year] ?? 0;
                $points = (float) $participation->points;
                $delta = max(0, $points - $base);

                $previousByDriverSeason[$participation->driver_id][$year] = $points;
                $teamPoints[$participation->team_id] = ($teamPoints[$participation->team_id] ?? 0) + $delta;
            }

            $raceTeamIds = $rows->pluck('team_id')->filter()->unique();
            foreach ($raceTeamIds as $teamId) {
                $teamPoints[$teamId] ??= 0;
            }

            $position = collect($teamPoints)
                ->map(fn ($points, $teamId) => ['id' => (int) $teamId, 'points' => $points])
                ->sort(
                    fn ($teamA, $teamB) => $teamB['points'] <=> $teamA['points'] ?: $teamA['id'] <=> $teamB['id']
                )
                ->pluck('id')
                ->search($this->team->id);

            if ($position !== false) {
                $result[] = [
                    'position' => (string) ($position + 1),
                    'name' => $race->name,
                    'date' => $race->date,
                ];
            }
        }

        return collect($result);
    }

    public function pointsHistory(?string $season = null): Collection
    {
        $teamRaceIds = $this->team
            ->participations()
            ->whereHas('race', fn ($q) => $q->when($season, fn ($w) => $w->whereYear('date', $season)))
            ->pluck('race_id')
            ->unique();

        if ($teamRaceIds->isEmpty()) {
            return collect();
        }

        $participations = Participation::with('race')
            ->whereIn('race_id', $teamRaceIds)
            ->whereNotNull('team_id')
            ->get()
            ->sortBy(fn ($p) => $p->race->date);

        $races = $participations
            ->groupBy(fn ($p) => $p->race->id)
            ->sortBy(fn ($group) => $group->first()->race->date);

        $neutral = (float) SyncRaceParticipations::neutralRating()['points'];
        $previousByDriverSeason = [];
        $teamRunning = [];

        $result = [];

        foreach ($races as $raceId => $rows) {
            $race = $rows->first()->race;

            $deduped = $rows
                ->groupBy(fn ($p) => $p->driver_id)
                ->map(fn ($driverRows) => $driverRows->sortByDesc('points')->first());

            foreach ($deduped as $row) {
                $year = substr($row->race->date, 0, 4);
                $base = $previousByDriverSeason[$row->driver_id][$year] ?? $neutral;

                $delta = (float) $row->points - $base;

                $previousByDriverSeason[$row->driver_id][$year] = (float) $row->points;

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
