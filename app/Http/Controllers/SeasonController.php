<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;
use Inertia\Inertia;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = Race::seasons();

        $data = $seasons->map(function ($season) {
            return [
                'season' => $season,
                'driverResults' => Participation::seasonDriversClasification($season),
                'teamResults' => Participation::seasonTeamsClasification($season),
                'races' => Participation::seasonRaces($season),
                'drivers' => Participation::seasonDrivers($season),
                'teams' => Participation::seasonTeams($season),
            ];
        });

        return Inertia::render('seasons/index', [
            'seasons' => $data,
        ]);
    }

    public function show(string $season)
    {
        abort_if(!Race::seasons()->contains($season), 404);

        $driverSeasonPointsHistory = Driver::whereHas('participations.race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->get()
            ->map(function ($driver) use ($season) {
                return [
                    'driver' => $driver,
                    'pointsHistory' => $driver->pointsHistory($season)
                ];
            })->values();

        $teamSeasonPointsHistory = Team::whereHas('participations.race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->get()
            ->map(function ($team) use ($season) {
                return [
                    'team' => $team,
                    'pointsHistory' => $team->pointsHistory($season)
                ];
            })->values();

        $data =  [
            'season' => $season,
            'driverResults' => Participation::seasonDriversClasification($season),
            'driversPoints' => $driverSeasonPointsHistory,
            'teamResults' => Participation::seasonTeamsClasification($season),
            'teamsPoints' => $teamSeasonPointsHistory,
        ];

        return Inertia::render('seasons/show', ['season' => $data]);
    }
}
