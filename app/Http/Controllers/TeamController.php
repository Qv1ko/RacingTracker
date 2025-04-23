<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Race;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Http\Requests\Team\StoreRequest;
use App\Http\Requests\Team\UpdateRequest;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function index(Request $req)
    {
        $season = $req->query('season');

        if ($season) {
            if ($season === 'all') {
                $data = Team::orderBy('name', 'asc')->get();
            } else {
                $data = Team::whereHas('participations.race', function ($query) use ($season) {
                    $query->whereYear('date', $season);
                })
                    ->orderBy('name', 'asc')
                    ->get();
            }
        } else {
            $latestYear = Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)"));
            $data = Team::whereHas('participations.race', function ($query) use ($latestYear) {
                $query->whereYear('date', $latestYear);
            })
                ->orderBy('name', 'asc')
                ->get();
        }

        $seasons = Race::pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        return Inertia::render('teams/index', [
            'seasons' => $seasons,
            'season' => $season,
            'teams' => $data,
        ]);
    }

    public function show(string $id)
    {
        $team = Team::findOrFail($id);
        return Inertia::render('teams/show', ['team' => $team]);
    }

    public function create()
    {
        return Inertia::render('teams/create');
    }

    public function store(StoreRequest $req)
    {
        $team = Team::create($req->validated());
        return Inertia::render('teams/show', ['team' => $team]);
    }

    public function edit(Team $team)
    {
        return Inertia::render('teams/edit', [
            'team' => $team,
        ]);
    }

    public function update(UpdateRequest $req, $id)
    {
        $team = Team::findOrFail($id);
        $team->update($req->validated());
        return Inertia::render('teams/show', ['team' => $team]);
    }

    public function destroy(string $id)
    {
        Team::findOrFail($id)->delete();
        return back();
    }
}
