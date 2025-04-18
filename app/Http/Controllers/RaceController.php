<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\Team;
use App\Models\Race;
use Illuminate\Support\Carbon;
use App\Http\Requests\Race\StoreRequest;
use App\Http\Requests\Race\UpdateRequest;
use Inertia\Inertia;

class RaceController extends Controller
{
    public function index(Request $req)
    {
        $season = $req->query('season');

        if ($season) {
            if ($season === 'all') {
                $data = Race::orderBy('name', 'asc')->get();
            } else {
                $data = Race::where('date', '>=', Carbon::parse($season)->startOfYear())
                    ->where('date', '<=', Carbon::parse($season)->endOfYear())
                    ->get();
            }
        } else {
            $data = Race::whereYear('date', Carbon::now()->year)
                ->get();
        }

        $seasons = Race::select('date')->distinct();

        return Inertia::render('races/index', [
            'seasons' => $seasons,
            'season' => $season,
            'races' => $data,
        ]);
    }

    public function show(string $id)
    {
        $race = Race::findOrFail($id);
        return Inertia::render('races/show', ['race' => $race]);
    }

    public function create()
    {
        $drivers = Driver::where('status', true)->get();
        $teams = Team::where('status', true)->get();

        return Inertia::render('races/create', ['drivers' => $drivers, 'teams' => $teams]);
    }

    public function store(StoreRequest $req)
    {
        foreach ($req->result as $participant) {
            dd($participant);
        }
        $race = Race::create($req->validated());
        return Inertia::render('races/show', ['race' => $race]);
    }

    public function edit(Race $race)
    {
        return Inertia::render('races/edit', [
            'race' => $race,
        ]);
    }

    public function update(UpdateRequest $req, $id)
    {
        $race = Race::findOrFail($id);
        $race->update($req->validated());
        return Inertia::render('races/show', ['race' => $race]);
    }

    public function destroy(string $id)
    {
        Race::findOrFail($id)->delete();
        return back();
    }
}
