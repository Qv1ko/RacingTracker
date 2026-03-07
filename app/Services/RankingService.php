<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RankingService
{
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
                    ->filter(fn ($p) => $p->race && $p->race->season == $season);

                $points = $seasonParticipations
                    ->sortByDesc(fn ($p) => $p->race->date)
                    ->unique('driver_id')
                    ->avg('points');

                $previousSeason = $this->previousSeason($team->participations, $season);

                $startingPoints = $team->participations
                    ->filter(fn ($p) => $p->race && $p->race->season == $previousSeason)
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
                is_null($a['sort_position']) && ! is_null($b['sort_position']) => 1,
                ! is_null($a['sort_position']) && is_null($b['sort_position']) => -1,
                is_null($a['sort_position']) && is_null($b['sort_position']) => 0,
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
            fn ($q) => $q->inSeason($season)
        )
            ->count();
    }

    public function seasonTeamsCount(string $season): int
    {
        return Team::whereHas(
            'participations.race',
            fn ($q) => $q->inSeason($season)
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
            fn ($q) => $q->inSeason($season)
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
