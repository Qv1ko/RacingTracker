<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Services\DriverStatsService;
use App\Services\RankingService;
use App\Support\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $req)
    {
        $seasons = Race::seasons();
        $season = $req->query('season');

        if ( ! in_array($season, $seasons->all())) {
            $season = $seasons->first() ?? 'all';
        }

        $lastRace = Race::whereYear('date', $season)
            ->orderBy('date', 'desc')
            ->first();

        $driverSeasonPointsHistory = Driver::whereHas('participations.race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->get()
            ->map(function ($driver) use ($season) {
                return [
                    'driver' => $driver,
                    'pointsHistory' => (new DriverStatsService($driver))->pointsHistory($season),
                ];
            })->values();

        $ranking = new RankingService;

        $teamWins = Participation::query()
            ->where('position', 1)
            ->whereNotNull('team_id')
            ->whereHas('race', fn ($query) => $query->whereYear('date', $season))
            ->with('team:id,name,nationality,color')
            ->get(['team_id'])
            ->filter(fn (Participation $participation) => $participation->team !== null)
            ->groupBy('team_id')
            ->map(function ($wins): array {
                $team = $wins->first()->team;

                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'color' => $team->color ?? Color::fromString($team->name),
                    'count' => $wins->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $seasonData = [
            'season' => $season,
            'driversPoints' => $driverSeasonPointsHistory,
            'teamStandings' => $lastRace ? $ranking->raceTeamStandings($lastRace) : [],
            'teamWins' => $teamWins,
        ];

        return Inertia::render('home', [
            'seasons' => $seasons,
            'season' => $seasonData,
        ]);
    }
}
