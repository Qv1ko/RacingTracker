<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RankingService
{
    /**
     * Season champions (biggest points gain) computed with a single pass
     * over the participations table, mirroring the semantics of
     * seasonDriversClassification() and seasonTeamsClassification().
     *
     * @return array{driver: Collection, team: Collection} Collections keyed by season
     */
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

    /** Lightweight summary of every season (counts + champions). */
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
        $lastPointsBeforeSeason = [];
        $champions = collect();

        foreach ($rowsBySeason as $season => $rows) {
            $lastPointsInSeason = [];

            foreach ($rows as $row) {
                $lastPointsInSeason[$row->driver_id] = (float) $row->points;
            }

            $bestDiff = null;
            $bestDriverId = null;

            foreach ($lastPointsInSeason as $driverId => $points) {
                $starting = $lastPointsBeforeSeason[$driverId] ?? null;
                $diff = $starting === null ? 0 : $points - $starting;

                if ($bestDiff === null || $diff > $bestDiff || ($diff === $bestDiff && $driverId < $bestDriverId)) {
                    $bestDiff = $diff;
                    $bestDriverId = $driverId;
                }
            }

            $champions[$season] = $bestDriverId;

            foreach ($lastPointsInSeason as $driverId => $points) {
                $lastPointsBeforeSeason[$driverId] = $points;
            }
        }

        return $champions;
    }

    private function computeTeamChampions(Collection $rowsBySeason): Collection
    {
        // Per team: chronological map of season => average of each driver's
        // latest points in that season (mirrors seasonTeamsClassification).
        $avgByTeamAndSeason = [];
        $seasonsOfTeam = [];
        $champions = collect();

        foreach ($rowsBySeason as $season => $rows) {
            $pointsByTeamAndDriver = [];

            foreach ($rows as $row) {
                if ($row->team_id === null) {
                    continue;
                }

                $pointsByTeamAndDriver[$row->team_id][$row->driver_id] = (float) $row->points;
            }

            foreach ($pointsByTeamAndDriver as $teamId => $pointsByDriver) {
                $avgByTeamAndSeason[$teamId][$season] = array_sum($pointsByDriver) / count($pointsByDriver);
                $seasonsOfTeam[$teamId][] = $season;
            }
        }

        foreach ($rowsBySeason->keys() as $season) {
            $bestDiff = null;
            $bestTeamId = null;

            foreach ($avgByTeamAndSeason as $teamId => $avgsBySeason) {
                if ( ! in_array($season, $seasonsOfTeam[$teamId], true)) {
                    continue;
                }

                $previousSeason = null;

                foreach ($seasonsOfTeam[$teamId] as $candidate) {
                    if ($candidate >= $season) {
                        break;
                    }

                    $previousSeason = $candidate;
                }

                $starting = $previousSeason !== null ? ($avgsBySeason[$previousSeason] ?? 0.0) : 0.0;
                $diff = $avgsBySeason[$season] - $starting;

                if ($bestDiff === null || $diff > $bestDiff || ($diff === $bestDiff && $teamId < $bestTeamId)) {
                    $bestDiff = $diff;
                    $bestTeamId = $teamId;
                }
            }

            $champions[$season] = $bestTeamId;
        }

        return $champions;
    }

    /**
     * Ranking position of a driver based on their latest points,
     * computed with two aggregate queries instead of the full ranking.
     */
    public function driverPosition(Driver $driver): ?int
    {
        $latestPointsByDriver = $this->latestPointsPerEntity('driver_id');

        $mine = collect($latestPointsByDriver)->firstWhere('id', $driver->id);

        if ($mine === null) {
            return null;
        }

        return count(array_filter($latestPointsByDriver, fn ($row) => $row->points > $mine->points)) + 1;
    }

    /** Ranking position of a team based on their latest points. */
    public function teamPosition(Team $team): ?int
    {
        $latestPointsByTeam = $this->latestPointsPerEntity('team_id');

        $mine = collect($latestPointsByTeam)->firstWhere('id', $team->id);

        if ($mine === null) {
            return null;
        }

        return count(array_filter($latestPointsByTeam, fn ($row) => $row->points > $mine->points)) + 1;
    }

    private function latestPointsPerEntity(string $entityColumn): array
    {
        return DB::table('participations as p')
            ->join('races as r', 'r.id', '=', 'p.race_id')
            ->joinSub(
                DB::table('participations as p2')
                    ->join('races as r2', 'r2.id', '=', 'p2.race_id')
                    ->whereNotNull("p2.{$entityColumn}")
                    ->groupBy("p2.{$entityColumn}")
                    ->selectRaw("p2.{$entityColumn} as id, max(r2.date) as last_date"),
                'latest',
                fn ($join) => $join
                    ->on("p.{$entityColumn}", '=', 'latest.id')
                    ->on('r.date', '=', 'latest.last_date'),
            )
            ->whereNotNull("p.{$entityColumn}")
            ->groupBy("p.{$entityColumn}")
            ->selectRaw("p.{$entityColumn} as id, max(p.points) as points")
            ->get()
            ->all();
    }

    public function driversRanking(): Collection
    {
        return Driver::whereHas('participations')
            ->get()
            ->map(fn ($driver) => [
                'driver' => $driver,
                'points' => (new DriverStatsService($driver))->lastPoints(),
            ])
            ->sortByDesc('points')
            ->values()
            ->map(fn ($item, $key) => array_merge($item, ['position' => $key + 1]));
    }

    public function teamsRanking(): Collection
    {
        return Team::whereHas('participations')
            ->get()
            ->map(fn ($team) => [
                'team' => $team,
                'points' => (new TeamStatsService($team))->lastPoints(),
            ])
            ->sortByDesc('points')
            ->values()
            ->map(fn ($item, $key) => array_merge($item, ['position' => $key + 1]));
    }

    public function seasonDriversClassification(string $season): Collection
    {
        return Driver::whereHas('participations.race', fn ($q) => $q->whereYear('date', $season))
            ->get()
            ->map(function ($driver) use ($season) {
                $statsService = new DriverStatsService($driver);
                $previousSeason = $this->previousSeason($driver->participations()->with('race')->get(), $season);

                $points = (float) ($statsService->lastPoints($season));
                $startingPoints = (float) $statsService->lastPoints($previousSeason);

                return [
                    'driver' => $driver,
                    'points' => $points,
                    'pointsDiff' => $points - $startingPoints,
                    'startingPoints' => $startingPoints,
                ];
            })
            ->sortByDesc('pointsDiff')
            ->values()
            ->map(fn ($item, $index) => array_merge(['position' => $index + 1], $item));
    }

    public function seasonTeamsClassification(string $season): Collection
    {
        return Team::whereHas('participations.race', fn ($q) => $q->whereYear('date', $season))
            ->with(['participations.race', 'participations.driver'])
            ->get()
            ->map(function ($team) use ($season) {
                $seasonParticipations = $team->participations
                    ->filter(fn ($p) => $p->race->season == $season);

                $points = $seasonParticipations
                    ->sortByDesc(fn ($p) => $p->race->date)
                    ->unique('driver_id')
                    ->avg('points');

                $previousSeason = $this->previousSeason($team->participations, $season);

                $startingPoints = $team->participations
                    ->filter(fn ($p) => $previousSeason !== null && $p->race->season == $previousSeason)
                    ->sortByDesc(fn ($p) => $p->race->date)
                    ->unique('driver_id')
                    ->avg('points');

                return [
                    'team' => $team,
                    'points' => (float) $points,
                    'pointsDiff' => (float) ($points - $startingPoints),
                    'startingPoints' => (float) $startingPoints,
                ];
            })
            ->sortByDesc('pointsDiff')
            ->values()
            ->map(fn ($item, $index) => array_merge(['position' => $index + 1], $item));
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

    private function previousSeason(Collection $participations, string $season): ?string
    {
        $last = $participations
            ->filter(fn ($p) => $p->race && Carbon::parse($p->race->date)->year < (int) $season)
            ->sortByDesc(fn ($p) => $p->race->date)
            ->first();

        return $last ? Carbon::parse($last->race->date)->format('Y') : null;
    }
}
