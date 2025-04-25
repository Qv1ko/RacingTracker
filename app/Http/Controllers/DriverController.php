<?php

namespace App\Http\Controllers;

use App\Http\Requests\Driver\StoreRequest;
use App\Http\Requests\Driver\UpdateRequest;
use Carbon\Carbon;
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
        $seasons = Race::pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        $season = $req->query('season');

        if (!$season) {
            $season = Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)"));
        }

        if ($season === 'all') {
            $drivers = Driver::orderBy('surname', 'asc')->get();
        } else {
            $drivers = Driver::whereHas('participations.race', function ($query) use ($season) {
                $query->whereYear('date', $season);
            })
                ->orderBy('surname', 'asc')
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
                'races' => $driver->races($season),
                'wins' => $driver->wins($season),
                'second_positions' => $driver->secondPositions($season),
                'third_positions' => $driver->thirdPositions($season),
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

        $driver =  [
            'id' => $driver->id,
            'name' => $driver->name,
            'surname' => $driver->surname,
            'nationality' => $driver->nationality,
            'status' => $driver->status,
            'teams' => $driver->teams(),
            'races' => $driver->races(),
            'wins' => $driver->wins(),
            'second_positions' => $driver->secondPositions(),
            'third_positions' => $driver->thirdPositions(),
            'primaryStats' => [],
            'secondaryStats' => [],
            'activity' => $driver->activity(),
            'seasons' => [],
            'racesWon' => [],
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
        return Inertia::render('drivers/show', ['driver' => $driver]);
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
        return Inertia::render('drivers/show', ['driver' => $driver]);
    }

    public function destroy(string $id)
    {
        Driver::findOrFail($id)->delete();
        return back();
    }
}
