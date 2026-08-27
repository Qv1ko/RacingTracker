<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;
use App\Services\DriverStatsService;
use App\Services\RacePresenter;
use App\Services\RankingService;
use App\Services\TeamStatsService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SeasonController extends Controller
{
    public function __construct(private RacePresenter $presenter, private RankingService $ranking) {}

    public function index()
    {
        return Inertia::render('seasons/index', [
            'seasons' => $this->ranking->seasonsSummary(),
        ]);
    }

    public function show(string $season)
    {
        abort_if( ! Race::seasons()->contains($season), 404);

        $ranking = new RankingService;

        $races = Race::whereYear('date', $season)
            ->orderBy('date', 'asc')
            ->with(['participations.driver', 'participations.team'])
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
                    'pointsHistory' => (new DriverStatsService($driver))->pointsHistory($season),
                ];
            })->values();

        $teamSeasonPointsHistory = Team::whereHas('participations.race', function ($query) use ($season) {
            $query->whereYear('date', $season);
        })
            ->get()
            ->map(function ($team) use ($season) {
                return [
                    'team' => $team,
                    'pointsHistory' => (new TeamStatsService($team))->pointsHistory($season),
                ];
            })->values();

        $lastRace = $races->last();
        $driverResults = $ranking->seasonDriversClassification($season);
        $driverOrder = $driverResults->pluck('driver.id')->flip();
        $positionDrivers = $races
            ->flatMap(fn (Race $race) => $race->participations)
            ->filter(fn (Participation $participation) => $participation->driver)
            ->map(fn (Participation $participation) => $participation->driver)
            ->unique('id')
            ->sortBy(fn (Driver $driver) => $driverOrder[$driver->id] ?? PHP_INT_MAX)
            ->values();

        $positionTracker = [
            'drivers' => $positionDrivers->map(fn (Driver $driver) => [
                'id' => $driver->id,
                'key' => 'driver_'.$driver->id,
                'name' => strtoupper(substr($driver->name, 0, 1)).'. '.$driver->surname,
                'color' => $driver->color,
            ])->values(),
            'data' => $races->map(function (Race $race): array {
                $row = [
                    'race' => $race->name,
                    'date' => $race->date,
                ];

                foreach ($race->participations as $participation) {
                    if ($participation->driver === null) {
                        continue;
                    }

                    $row['driver_'.$participation->driver_id] = $participation->position === null
                        ? null
                        : (int) $participation->position;
                }

                return $row;
            })->values(),
        ];

        $data = [
            'season' => $season,
            'info' => [
                'firstRace' => $races->first(),
                'lastRace' => $lastRace,
                'mostWins' => $winners->where('wins', $maxVictories)->values(),
                'mostPodiums' => $podiums->where('podiums', $maxPodiums)->values(),
                'mostWithoutPosition' => $withoutPosition->where('withoutPosition', $maxWithoutPosition)->values(),
                'racesCount' => $ranking->seasonRacesCount($season),
                'championDriver' => $ranking->seasonDriversClassification($season)->where('position', 1)->first()['driver'],
                'championTeam' => $ranking->seasonTeamsClassification($season)->where('position', 1)->first()['team'],
            ],
            'driverStandings' => $lastRace ? $ranking->raceDriverStandings($lastRace) : [],
            'driverResults' => $driverResults,
            'positionTracker' => $positionTracker,
            'driversPoints' => $driverSeasonPointsHistory,
            'teamStandings' => $lastRace ? $ranking->raceTeamStandings($lastRace) : [],
            'teamResults' => $ranking->seasonTeamsClassification($season),
            'teamsPoints' => $teamSeasonPointsHistory,
            'races' => $races->map(fn (Race $race) => $this->presenter->present($race)),
        ];

        return Inertia::render('seasons/show', ['season' => $data]);
    }
}
