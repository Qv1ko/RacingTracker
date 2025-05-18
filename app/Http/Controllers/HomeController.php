<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Race;
use App\Models\Driver;
use App\Models\Participation;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $req)
    {
        $seasons = Race::seasons();
        $season = $req->query('season');

        if (!in_array($season, $seasons->all())) {
            $season = Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)")) ?? 'all';
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
                    'pointsHistory' => $driver->pointsHistory($season)
                ];
            })->values();

        $seasonData =  [
            'season' => $season,
            'driversPoints' => $driverSeasonPointsHistory,
            'teamStandings' => $lastRace?->id ? Participation::raceTeamStandings($lastRace->id) : [],
        ];

        return Inertia::render('home', [
            'seasons' => $seasons,
            'season' => $seasonData,
            'drivers' => [
                'ranking' => Participation::driversRanking(),
            ],
            'teams' => [
                'ranking' => Participation::teamsRanking(),
            ],
        ]);
    }
}
