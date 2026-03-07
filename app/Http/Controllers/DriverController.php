<?php

namespace App\Http\Controllers;

use App\Http\Requests\Driver\StoreRequest;
use App\Http\Requests\Driver\UpdateRequest;
use App\Models\Driver;
use App\Models\Race;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function index(Request $req)
    {
        $seasons = Race::seasons();
        $season = $req->query('season');

        if ($season !== 'all' && ! in_array($season, $seasons->all())) {
            $season = Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)")) ?? 'all';
        }

        if ($season === 'all') {
            $drivers = Driver::orderByRaw('LOWER(surname) asc')->get();
        } else {
            $drivers = Driver::whereHas('participations.race', function ($query) use ($season) {
                $query->whereYear('date', $season);
            })
                ->orderByRaw('LOWER(surname) asc')
                ->get();
        }

        $data = $drivers->map(function ($driver) use ($season) {
            return [
                'id' => $driver->id,
                'name' => $driver->name,
                'surname' => $driver->surname,
                'nationality' => $driver->nationality,
                'status' => $driver->status,
                'teams' => $driver->teams(),
                'races' => $driver->races()->count(),
                'wins' => $driver->stats()->getPositionsCount($season),
                'second_positions' => $driver->stats()->getPositionsCount($season, 2),
                'third_positions' => $driver->stats()->getPositionsCount($season, 3),
                'points' => $driver->stats()->lastPoints($season),
            ];
        });

        return Inertia::render('drivers/index', [
            'seasons' => $seasons,
            'season' => $season,
            'drivers' => $data,
        ]);
    }

    public function show(Driver $driver)
    {
        $ranking = new RankingService;

        $driverStats = $driver->stats();
        $racesCount = $driver->races()->count();
        $winsCount = $driverStats->getPositionsCount();
        $podiumsCount = $driverStats->podiums()->count();

        $driver = [
            'id' => $driver->id,
            'name' => $driver->name,
            'surname' => $driver->surname,
            'nationality' => $driver->nationality,
            'status' => $driver->status,
            'teams' => $driver->teams,
            'races' => $racesCount,
            'wins' => $winsCount,
            'seasons' => $driverStats->seasons()->count(),
            'championshipsCount' => $driverStats->championships()?->count() ?? 0,
            'points' => $driverStats->lastPoints(),
            'maxPoints' => $driverStats->pointsHistory()->max('points'),
            'activity' => $driverStats->activity(),
            'info' => [
                'firstRace' => $driver->races->first(),
                'lastRace' => $driver->races->last(),
                'firstWin' => $driver->races()->where('position', 1)->first(),
                'lastWin' => $driver->races()->where('position', 1)->get()->last(),
                'winPercentage' => $racesCount > 0
                    ? round($winsCount / $racesCount * 100, 2)
                    : null,
                'podiums' => $podiumsCount,
                'podiumPercentage' => $racesCount > 0
                    ? round($podiumsCount / $racesCount * 100, 2)
                    : null,
                'withoutPosition' => $driver->participations()
                    ->where('position', null)
                    ->count(),
                'ranking' => $ranking->driversRanking()->firstWhere('driver.id', $driver->id),
                'championships' => $driverStats->championships(),
            ],
            'pointsHistory' => $driverStats->pointsHistory(),
            'positionsHistory' => $driverStats->countForPosition(),
            'teammates' => $driverStats->teammates(),
        ];

        return Inertia::render('drivers/show', ['driver' => $driver]);
    }

    public function create()
    {
        return Inertia::render('drivers/create');
    }

    public function store(StoreRequest $req)
    {
        $driver = Driver::create($req->validated());

        return redirect()->route('drivers.show', $driver->id);
    }

    public function edit(Driver $driver)
    {
        return Inertia::render('drivers/edit', [
            'driver' => $driver,
        ]);
    }

    public function update(UpdateRequest $req, Driver $driver)
    {
        $driver->update($req->validated());

        return redirect()->route('drivers.show', $driver->id);
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return back();
    }
}
