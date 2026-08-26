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

        $driversById = Driver::query()
            ->whereIn('id', $championDriverIdBySeason->filter()->values())
            ->get()
            ->keyBy('id');

        $teamsById = Team::query()
            ->whereIn('id', $championTeamIdBySeason->filter()->values())
            ->get()
            ->keyBy('id');

        return $rowsBySeason
            ->keys()
            ->map(function ($season) use ($rowsBySeason, $racesCountBySeason, $championDriverIdBySeason, $championTeamIdBySeason, $driversById, $teamsById) {
                $rowsInSeason = $rowsBySeason->get($season);
                $driverId = $championDriverIdBySeason->get($season);
                $teamId = $championTeamIdBySeason->get($season);

                return [
                    'season' => $season,
                    'racesCount' => $racesCountBySeason->get($season, 0),
                    'driversCount' => $rowsInSeason->pluck('driver_id')->unique()->count(),
                    'teamsCount' => $rowsInSeason->pluck('team_id')->filter()->unique()->count(),
                    'championDriver' => $driverId !== null ? $driversById->get($driverId) : null,
                    'championTeam' => $teamId !== null ? $teamsById->get($teamId) : null,
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

            $sumByTeam = [];

            foreach ($pointsByTeamAndDriver as $teamId => $pointsByDriver) {
                $sumByTeam[$teamId] = array_sum($pointsByDriver);
            }

            $bestTeamId = null;
            $bestSum = null;

            foreach ($sumByTeam as $teamId => $sum) {
                if ($bestSum === null || $sum > $bestSum || ($sum === $bestSum && $teamId < $bestTeamId)) {
                    $bestSum = $sum;
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
        $championships = $this->championsBySeason()['driver']->filter()->countBy();

        return $this->rankPosition($points, $championships, $driver->id);
    }

    public function teamPosition(Team $team): ?int
    {
        $points = $this->historicalTeamPoints();
        $championships = $this->championsBySeason()['team']->filter()->countBy();

        return $this->rankPosition($points, $championships, $team->id);
    }

    private function rankPosition(Collection $points, Collection $championships, int $id): ?int
    {
        if (! $points->has($id)) {
            return null;
        }

        return $points
            ->map(fn ($sum, $key) => [
                'championships' => $championships->get($key, 0),
                'points' => $sum,
            ])
            ->sortByDesc(fn ($entry) => [$entry['championships'], $entry['points']])
            ->keys()
            ->search($id) + 1;
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
                $lastSumBySeason = [];

                foreach ($teamRows->groupBy('race_id') as $raceRows) {
                    $sum = $raceRows
                        ->groupBy('driver_id')
                        ->map(fn ($rows) => $rows->sortByDesc('date')->first())
                        ->sum(fn ($row) => (float) $row->points);

                    $lastSumBySeason[substr($raceRows->first()->date, 0, 4)] = $sum;
                }

                return [$teamId => round(array_sum($lastSumBySeason), 3)];
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
        $driverChampionships = $this->championsBySeason()['driver']->filter()->countBy();

        return Driver::whereIn('id', $points->keys()->all())
            ->get()
            ->map(fn ($driver) => [
                'driver' => $driver,
                'points' => $points->get($driver->id),
                'championships' => $driverChampionships->get($driver->id, 0),
            ])
            ->sortByDesc(fn ($item) => [$item['championships'], $item['points'] ?? 0])
            ->values()
            ->map(fn ($item, $key) => array_merge($item, ['position' => $key + 1]));
    }

    public function teamsRanking(): Collection
    {
        $points = $this->historicalTeamPoints();
        $teamChampionships = $this->championsBySeason()['team']->filter()->countBy();

        return Team::whereIn('id', $points->keys()->all())
            ->get()
            ->map(fn ($team) => [
                'team' => $team,
                'points' => $points->get($team->id),
                'championships' => $teamChampionships->get($team->id, 0),
            ])
            ->sortByDesc(fn ($item) => [$item['championships'], $item['points'] ?? 0])
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
                    ->sum('points');

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
                $previousTeamPoints = $previousTeamPointsByTeam[$participation->team_id] ?? 0;

                return [
                    'sort_position' => $participation->position,
                    'position' => $participation->status,
                    'driver' => $participation->driver,
                    'points' => $participation->points,
                    'pointsDiff' => $participation->points - ($previousPoints ?? 0),
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
                'points' => $group->sortByDesc('race.date')->unique('driver_id')->sum('points'),
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
            ->map(fn ($group) => $group->sortByDesc('race.date')->unique('driver_id')->sum('points'));
    }
}
