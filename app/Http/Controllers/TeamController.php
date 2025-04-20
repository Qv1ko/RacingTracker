<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Race;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Http\Requests\Team\StoreRequest;
use App\Http\Requests\Team\UpdateRequest;

class TeamController extends Controller
{
    public function index(Request $req)
    {
        $season = $req->query('season');

        if ($season) {
            if ($season === 'all') {
                $data = Team::orderBy('name', 'asc')->get();
            } else {
                $data = Team::join('participations', 'teams.id', '=', 'participations.team_id')
                    ->join('races', 'participations.race_id', '=', 'races.id')
                    ->where('races.date', '>=', Carbon::parse($season)->startOfYear())
                    ->where('races.date', '<=', Carbon::parse($season)->endOfYear())
                    ->select('teams.*')
                    ->get();
            }
        } else {
            $data = Team::join('participations', 'teams.id', '=', 'participations.team_id')
                ->join('races', 'participations.race_id', '=', 'races.id')
                ->whereYear('races.date', Carbon::now()->year)
                ->select('teams.*')
                ->get();
        }

        $seasons = Race::pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y'))
            ->unique();;

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
