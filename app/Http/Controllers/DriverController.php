<?php

namespace App\Http\Controllers;

use App\Http\Requests\Driver\StoreRequest;
use App\Http\Requests\Driver\UpdateRequest;
use App\Models\Driver;
use App\Models\Race;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Participation;

class DriverController extends Controller
{
    public function index(Request $req)
    {
        $seasons = Race::seasons();
        $season = $req->query('season');

        if ($season !== 'all' && !in_array($season, $seasons->all())) {
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
                'teams' => $driver->teams($season),
                'races' => $driver->races($season)->count(),
                'wins' => $driver->wins($season)->count(),
                'second_positions' => $driver->secondPositions($season)->count(),
                'third_positions' => $driver->thirdPositions($season)->count(),
                'points' => $driver->lastPoints($season),
            ];
        });

        return Inertia::render('drivers/index', [
            'seasons' => $seasons,
            'season' => $season,
            'drivers' => $data,
        ]);
    }

    public function show(string $id)
    {
        $driver = Driver::findOrFail($id);

        $racesCount = $driver->races()->count();
        $winsCount = $driver->wins()->count();
        $podiumsCount = $driver->podiums()->count();

        $driver =  [
            'id' => $driver->id,
            'name' => $driver->name,
            'surname' => $driver->surname,
            'nationality' => $driver->nationality,
            'status' => $driver->status,
            'teams' => $driver->teams(),
            'races' => $racesCount,
            'wins' => $winsCount,
            'seasons' => $driver->seasons()->count(),
            'championshipsCount' => $driver->championships()?->count() ?? 0,
            'points' => $driver->lastPoints(),
            'maxPoints' => $driver->pointsHistory()->max('points'),
            'activity' => $driver->activity(),
            'info' => [
                'firstRace' => $driver->races()->first(),
                'lastRace' => $driver->races()->last(),
                'firstWin' => $driver->wins()->first(),
                'lastWin' => $driver->wins()->last(),
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
                'raking' => Participation::driversRanking()->firstWhere('driver.id', $driver->id),
                'championships' => $driver->championships(),
            ],
            'pointsHistory' => $driver->pointsHistory(),
            'positionsHistory' => $driver->countForPosition(),
            'teammates' => $driver->teammates(),
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

    public function update(UpdateRequest $req, $id)
    {
        $driver = Driver::findOrFail($id);
        $driver->update($req->validated());
        return redirect()->route('drivers.show', $driver->id);
    }

    public function destroy(string $id)
    {
        Driver::findOrFail($id)->delete();
        return back();
    }
}
