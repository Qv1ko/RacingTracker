<?php

namespace App\Http\Controllers;

use App\Http\Requests\Driver\StoreRequest;
use App\Http\Requests\Driver\UpdateRequest;
use Carbon\Carbon;
use App\Models\Driver;
use App\Models\Race;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function index(Request $req)
    {
        $season = $req->query('season');

        if ($season) {
            if ($season === 'all') {
                $data = Driver::orderBy('surname', 'asc')->get();
            } else {
                $data = Driver::join('participations', 'drivers.id', '=', 'participations.driver_id')
                    ->join('races', 'participations.race_id', '=', 'races.id')
                    ->where('races.date', '>=', Carbon::parse($season)->startOfYear())
                    ->where('races.date', '<=', Carbon::parse($season)->endOfYear())
                    ->select('drivers.*')
                    ->get();
            }
        } else {
            $data = Driver::join('participations', 'drivers.id', '=', 'participations.driver_id')
                ->join('races', 'participations.race_id', '=', 'races.id')
                ->whereYear('races.date', Carbon::now()->year)
                ->select('drivers.*')
                ->get();
        }

        $seasons = Race::pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y'))
            ->unique();

        return Inertia::render('drivers/index', [
            'seasons' => $seasons,
            'season' => $season,
            'drivers' => $data,
        ]);
    }

    public function show(string $id)
    {
        $driver = Driver::findOrFail($id);
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
