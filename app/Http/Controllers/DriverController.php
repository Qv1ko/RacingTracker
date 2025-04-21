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

class DriverController extends Controller
{
    public function index(Request $req)
    {
        $season = $req->query('season');

        if ($season) {
            if ($season === 'all') {
                $data = Driver::orderBy('surname', 'asc')->get();
            } else {
                $data = Driver::whereHas('participations.race', function ($query) use ($season) {
                    $query->whereYear('date', $season);
                })
                    ->orderBy('surname', 'asc')
                    ->get();
            }
        } else {
            $latestYear = Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)"));

            $data = Driver::whereHas('participations.race', function ($query) use ($latestYear) {
                $query->whereYear('date', $latestYear);
            })
                ->orderBy('surname', 'asc')
                ->get();
        }

        $seasons = Race::pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

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
