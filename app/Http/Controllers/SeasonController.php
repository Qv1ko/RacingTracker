<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\Participation;
use App\Models\Driver;
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
}
