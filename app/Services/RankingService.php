<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RankingService
{
    /** @return array{driver: Collection, team: Collection} Collections keyed by season */
    public function championsBySeason(): array
    {
        $rows = DB::table('participations')
            ->join('races', 'races.id', '=', 'participations.race_id')
            ->orderBy('races.date')
            ->get([
                'participations.driver_id',
                'participations.team_id',
                'participations.points',
                'races.date',
            ]);

        $rowsBySeason = $rows->groupBy(fn ($row) => substr($row->date, 0, 4))->sortKeys();

        return [
            'driver' => $this->computeDriverChampions($rowsBySeason),
            'team' => $this->computeTeamChampions($rowsBySeason),
        ];
    }

    public function seasonsSummary(): Collection
    {
        $champions = $this->championsBySeason();

        $championDriverIdBySeason = $champions['driver'];
        $championTeamIdBySeason = $champions['team'];

        $racesCountBySeason = Race::query()->get(['date'])
            ->countBy(fn (Race $race) => substr($race->date, 0, 4));

        $rowsBySeason = DB::table('participations')
            ->join('races', 'races.id', '=', 'participations.race_id')
            ->get([
                'participations.driver_id',
                'participations.team_id',
                'races.date',
            ])
            ->groupBy(fn ($row) => substr($row->date, 0, 4));

        $championIds = collect($championDriverIdBySeason->values())
            ->merge($championTeamIdBySeason->values())
            ->filter()
            ->unique()
            ->values();

        $modelsById = Driver::query()->whereIn('id', $championIds)->get()->keyBy('id');

        return $rowsBySeason
            ->keys()
            ->map(function ($season) use ($rowsBySeason, $racesCountBySeason, $championDriverIdBySeason, $championTeamIdBySeason, $modelsById) {
                $rowsInSeason = $rowsBySeason->get($season);
                $driverId = $championDriverIdBySeason->get($season);
                $teamId = $championTeamIdBySeason->get($season);

                return [
                    'season' => $season,
                    'racesCount' => $racesCountBySeason->get($season, 0),
                    'driversCount' => $rowsInSeason->pluck('driver_id')->unique()->count(),
                    'teamsCount' => $rowsInSeason->pluck('team_id')->filter()->unique()->count(),
                    'championDriver' => $driverId !== null ? $modelsById->get($driverId) : null,
                    'championTeam' => $teamId !== null ? $modelsById->get($teamId) : null,
                ];
            })
            ->sortByDesc('season')
            ->values();
    }

    private function computeDriverChampions(Collection $rowsBySeason): Collection
    {
        $champions = collect();

        foreach ($rowsBySeason as $season => $rows) {
            $finalPoints = [];

            foreach ($rows as $row) {
                $finalPoints[$row->driver_id] = (float) $row->points;
            }

            $bestDriverId = null;
            $bestPoints = null;

            foreach ($finalPoints as $driverId => $points) {
                if ($bestPoints === null || $points > $bestPoints || ($points === $bestPoints && $driverId < $bestDriverId)) {
                    $bestPoints = $points;
                    $bestDriverId = $driverId;
                }
            }

            $champions[$season] = $bestDriverId;
        }

        return $champions;
    }

    private function computeTeamChampions(Collection $rowsBySeason): Collection
    {
        $champions = collect();

        foreach ($rowsBySeason as $season => $rows) {
            $pointsByTeamAndDriver = [];

            foreach ($rows as $row) {
                if ($row->team_id === null) {
                    continue;
                }

                $pointsByTeamAndDriver[$row->team_id][$row->driver_id] = (float) $row->points;
            }

            $avgByTeam = [];

            foreach ($pointsByTeamAndDriver as $teamId => $pointsByDriver) {
                $avgByTeam[$teamId] = array_sum($pointsByDriver) / count($pointsByDriver);
            }

            $bestTeamId = null;
            $bestAvg = null;

            foreach ($avgByTeam as $teamId => $avg) {
                if ($bestAvg === null || $avg > $bestAvg || ($avg === $bestAvg && $teamId < $bestTeamId)) {
                    $bestAvg = $avg;
                    $bestTeamId = $teamId;
                }
            }

            $champions[$season] = $bestTeamId;
        }

        return $champions;
    }

    public function driverPosition(Driver $driver): ?int
    {
        $points = $this->historicalDriverPoints();

        $mine = $points->get($driver->id);

        if ($mine === null) {
            return null;
        }

        return $points->filter(fn ($sum) => $sum > $mine)->count() + 1;
    }

    public function teamPosition(Team $team): ?int
    {
        $points = $this->historicalTeamPoints();

        $mine = $points->get($team->id);

        if ($mine === null) {
            return null;
        }

        return $points->filter(fn ($sum) => $sum > $mine)->count() + 1;
    }

    public function historicalDriverPoints(): Collection
    {
        return DB::table('participations')
            ->join('races', 'races.id', '=', 'participations.race_id')
            ->orderBy('races.date')
            ->get([
                'participations.driver_id',
                'participations.points',
                'races.date',
            ])
            ->groupBy('driver_id')
            ->mapWithKeys(fn ($rows, $driverId) => [$driverId => $this->sumOfSeasonEnds($rows)]);
    }

    public function historicalTeamPoints(): Collection
    {
        return DB::table('participations')
            ->join('races', 'races.id', '=', 'participations.race_id')
            ->whereNotNull('participations.team_id')
            ->orderBy('races.date')
            ->get([
                'participations.team_id',
                'participations.race_id',
                'participations.driver_id',
                'participations.points',
                'races.date',
            ])
            ->groupBy('team_id')
            ->mapWithKeys(function ($teamRows, $teamId) {
                $lastAvgBySeason = [];

                foreach ($teamRows->groupBy('race_id') as $raceRows) {
                    $avg = $raceRows->avg(fn ($row) => (float) $row->points);
                    $lastAvgBySeason[substr($raceRows->first()->date, 0, 4)] = $avg;
                }

                return [$teamId => round(array_sum($lastAvgBySeason), 3)];
            });
    }

    private function sumOfSeasonEnds(Collection $rows): float
    {
        $lastPointsBySeason = [];

        foreach ($rows as $row) {
            $lastPointsBySeason[substr($row->date, 0, 4)] = (float) $row->points;
        }

        return round(array_sum($lastPointsBySeason), 3);
    }

    public function driversRanking(): Collection
    {
        $points = $this->historicalDriverPoints();

        return Driver::whereIn('id', $points->keys()->all())
            ->get()
            ->map(fn ($driver) => [
                'driver' => $driver,
                'points' => $points->get($driver->id),
            ])
            ->sortByDesc('points')
            ->values()
            ->map(fn ($item, $key) => array_merge($item, ['position' => $key + 1]));
    }

    public function teamsRanking(): Collection
    {
        $points = $this->historicalTeamPoints();

        return Team::whereIn('id', $points->keys()->all())
            ->get()
            ->map(fn ($team) => [
                'team' => $team,
                'points' => $points->get($team->id),
            ])
            ->sortByDesc('points')
            ->values()
            ->map(fn ($item, $key) => array_merge($item, ['position' => $key + 1]));
    }

    public function seasonDriversClassification(string $season): Collection
    {
        $classification = Driver::whereHas('participations.race', fn ($q) => $q->whereYear('date', $season))
            ->get()
            ->map(fn ($driver) => [
                'driver' => $driver,
                'points' => (new DriverStatsService($driver))->lastPoints($season) ?? 0.0,
            ])
            ->sortByDesc('points')
            ->values();

        if ($classification->isEmpty()) {
            return $classification;
        }

        $maxPoints = $classification->max('points');

        return $classification
            ->map(fn ($item, $index) => array_merge(
                ['position' => $index + 1],
                $item,
                ['gap' => round($maxPoints - $item['points'], 3)],
            ));
    }

    public function seasonTeamsClassification(string $season): Collection
    {
        $classification = Team::whereHas('participations.race', fn ($q) => $q->whereYear('date', $season))
            ->with(['participations.race'])
            ->get()
            ->map(function ($team) use ($season) {
                $points = $team->participations
                    ->filter(fn ($p) => $p->race->season == $season)
                    ->sortByDesc(fn ($p) => $p->race->date)
                    ->unique('driver_id')
                    ->avg('points');

                return [
                    'team' => $team,
                    'points' => (float) number_format((float) $points, 3),
                ];
            })
            ->sortByDesc('points')
            ->values();

        if ($classification->isEmpty()) {
            return $classification;
        }

        $maxPoints = $classification->max('points');

        return $classification
            ->map(fn ($item, $index) => array_merge(
                ['position' => $index + 1],
                $item,
                ['gap' => round($maxPoints - $item['points'], 3)],
            ));
    }

    public function raceResult(Race $race): Collection
    {
        $season = $race->season;
        $previousRace = Race::whereYear('date', $season)->where('date', '<', $race->date)->orderByDesc('date')->first();

        $teamPointsByTeam = $this->teamPointsUpTo($season, $race->date);
        $previousTeamPointsByTeam = $previousRace ? $this->teamPointsUpTo($season, $previousRace->date) : collect();

        return Participation::where('race_id', $race->id)
            ->with(['driver', 'team', 'race'])
            ->get()
            ->map(function ($participation) use ($race, $season, $teamPointsByTeam, $previousTeamPointsByTeam) {
                $previousPoints = Participation::where('driver_id', $participation->driver_id)
                    ->whereHas('race', fn ($q) => $q->whereYear('date', $season)->where('date', '<', $race->date))
                    ->orderByDesc(Race::select('date')->whereColumn('races.id', 'participations.race_id')->limit(1))
                    ->value('points');

                $currentTeamPoints = $teamPointsByTeam[$participation->team_id] ?? 0;
                $previousTeamPoints = $previousTeamPointsByTeam[$participation->team_id];

                return [
                    'sort_position' => $participation->position,
                    'position' => $participation->status,
                    'driver' => $participation->driver,
                    'points' => $participation->points,
                    'pointsDiff' => $participation->points - $previousPoints,
                    'team' => $participation->team,
                    'teamPoints' => $currentTeamPoints,
                    'teamPointsDiff' => $currentTeamPoints - $previousTeamPoints,
                ];
            })
            ->sort(fn ($a, $b) => match (true) {
                is_null($a['sort_position']) && is_null($b['sort_position']) => 0,
                is_null($a['sort_position']) => 1,
                is_null($b['sort_position']) => -1,
                default => $a['sort_position'] <=> $b['sort_position'],
            })
            ->values()
            ->map(fn ($item) => array_diff_key($item, ['sort_position' => null]));
    }

    public function raceDriverStandings(Race $race): Collection
    {
        $participations = Participation::whereHas('race', fn ($q) => $q->whereYear('date', $race->season)->where('date', '<=', $race->date))
            ->with(['driver', 'race'])
            ->get()
            ->groupBy('driver_id')
            ->map(fn ($group) => $group->sortByDesc('race.date')->first())
            ->sortByDesc('points');

        $maxPoints = $participations->max('points');

        return $participations->values()->map(fn ($participation, $index) => [
            'position' => $index + 1,
            'driver' => $participation->driver,
            'points' => $participation->points,
            'gap' => $participation->points - $maxPoints,
        ]);
    }

    public function raceTeamStandings(Race $race): Collection
    {
        $teams = Participation::whereHas('race', fn ($q) => $q->whereYear('date', $race->season)->where('date', '<=', $race->date))
            ->whereNotNull('team_id')
            ->with(['team', 'race'])
            ->get()
            ->groupBy('team_id')
            ->map(fn ($group) => [
                'team' => $group->first()->team,
                'points' => $group->sortByDesc('race.date')->unique('driver_id')->avg('points'),
            ])
            ->sortByDesc('points')
            ->values();

        $maxPoints = $teams->max('points');

        return $teams->map(fn ($team, $index) => [
            'position' => $index + 1,
            'team' => $team['team'],
            'points' => $team['points'],
            'gap' => $team['points'] - $maxPoints,
        ]);
    }

    public function seasonDriversCount(string $season): int
    {
        return Driver::whereHas(
            'participations.race',
            fn ($q) => $q->whereYear('date', $season)
        )
            ->count();
    }

    public function seasonTeamsCount(string $season): int
    {
        return Team::whereHas(
            'participations.race',
            fn ($q) => $q->whereYear('date', $season)
        )
            ->count();
    }

    public function seasonRacesCount(string $season): int
    {
        return Race::query()
            ->inSeason($season)
            ->count();
    }

    private function teamPointsUpTo(string $season, string $date): Collection
    {
        return Participation::whereHas(
            'race',
            fn ($q) => $q->whereYear('date', $season)
                ->where('date', '<=', $date)
        )
            ->whereNotNull('team_id')
            ->with(['race'])
            ->get()
            ->groupBy('team_id')
            ->map(fn ($group) => $group->sortByDesc('race.date')->unique('driver_id')->avg('points'));
    }
}
