<?php

namespace App\Http\Controllers;

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

        $seasons = Race::select('date')->distinct();

        return Inertia::render('drivers/index', [
            'seasons' => $seasons,
            'season' => $season,
            'drivers' => $data,
        ]);
    }

    public function show($id)
    {
        $driver = Driver::findOrFail($id);
        return Inertia::render('drivers/show', [$driver]);
    }

    public function create()
    {
        return Inertia::render('drivers/create');
    }

    public function store(Request $req)
    {
        $req->validated();
        $driver = Driver::create($req->all());
        return to_route('drivers.index');
    }

    public function edit(Request $req)
    {
        return Inertia::render('drivers/edit');
    }

    public function update(Request $req, $id)
    {
        $req->validated();
        Driver::findOrFail($id)->update($req->all());
        return to_route('drivers.show', $req->id);
    }

    public function destroy(string $id)
    {
        Driver::findOrFail($id)->delete();
        return back();
    }
}
