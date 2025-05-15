<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\get;

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
                'racesCount' => Participation::seasonRaces($season),
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

        $races = Race::whereYear('date', $season)
            ->orderBy('date', 'asc')
            ->get();

        $winners = Participation::whereHas('race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->where('position', 1)
            ->select('driver_id', DB::raw('COUNT(*) as wins'))
            ->with('driver')
            ->groupBy('driver_id')
            ->orderByDesc('wins')
            ->get();

        $maxVictories = $winners->first()->wins ?? 0;

        $podiums = Participation::whereHas('race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->where('position', '<=', 3)
            ->select('driver_id', DB::raw('COUNT(*) as podiums'))
            ->with('driver')
            ->groupBy('driver_id')
            ->orderByDesc('podiums')
            ->get();

        $maxPodiums = $podiums->first()->podiums ?? 0;

        $withoutPosition = Participation::whereHas('race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->whereNull('position')
            ->select('driver_id', DB::raw('COUNT(*) as withoutPosition'))
            ->with('driver')
            ->groupBy('driver_id')
            ->orderByDesc('withoutPosition')
            ->get();

        $maxWithoutPosition = $withoutPosition->first()->withoutPosition ?? 0;

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
            'info' => [
                'firstRace' => $races->first(),
                'lastRace' => $races->last(),
                'mostWins' => $winners->where('wins', $maxVictories)->values(),
                'mostPodiums' => $podiums->where('podiums', $maxPodiums)->values(),
                'mostWithoutPosition' => $withoutPosition->where('withoutPosition', $maxWithoutPosition)->values(),
                'racesCount' => Participation::seasonRaces($season),
                'championDriver' => Participation::seasonDriversClasification($season)->where('position', 1)->first()['driver'],
                'championTeam' => Participation::seasonTeamsClasification($season)->where('position', 1)->first()['team'],
            ],
            'driverStandings' => Participation::raceDriverStandings($races->last()->id),
            'driverResults' => Participation::seasonDriversClasification($season),
            'driversPoints' => $driverSeasonPointsHistory,
            'teamStandings' => Participation::raceTeamStandings($races->last()->id),
            'teamResults' => Participation::seasonTeamsClasification($season),
            'teamsPoints' => $teamSeasonPointsHistory,
            'races' => Race::whereYear('date', $season)
                ->orderBy('date', 'asc')
                ->get()
                ->map(function ($race) {
                    return [
                        'id' => $race->id,
                        'name' => $race->name,
                        'date' => $race->date,
                        'winner' => $race->participant(1),
                        'second' => $race->participant(2),
                        'third' => $race->participant(3),
                        'better' => $race->better(),
                    ];
                }),
        ];

        return Inertia::render('seasons/show', ['season' => $data]);
    }
}
