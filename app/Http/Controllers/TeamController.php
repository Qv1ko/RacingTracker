<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Race;
use Inertia\Inertia;
use App\Http\Requests\Team\StoreRequest;
use App\Http\Requests\Team\UpdateRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Participation;

class TeamController extends Controller
{
    public function index(Request $req)
    {
        $seasons = Race::seasons();
        $season = $req->query('season');

        if ($season !== 'all' && !in_array($season, $seasons->all())) {
            $season = Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)")) ?? 'all';
        }

        if ($season === 'all') {
            $teams = Team::orderByRaw('LOWER(name) asc')->get();
        } else {
            $teams = Team::whereHas('participations.race', function ($query) use ($season) {
                $query->whereYear('date', $season);
            })
                ->orderByRaw('LOWER(name) asc')
                ->get();
        }

        $data = $teams->map(function ($team) use ($season) {
            return [
                'id' => $team->id,
                'name' => $team->name,
                'nationality' => $team->nationality,
                'status' => $team->status,
                'drivers' => $team->drivers($season),
                'races' => $team->races($season)->count(),
                'wins' => $team->wins($season)->count(),
                'second_positions' => $team->secondPositions($season)->count(),
                'third_positions' => $team->thirdPositions($season)->count(),
                'points' => $team->lastPoints($season),
            ];
        });

        return Inertia::render('teams/index', [
            'seasons' => $seasons,
            'season' => $season,
            'teams' => $data,
        ]);
    }

    public function show(string $id)
    {
        $team = Team::findOrFail($id);

        $racesCount = $team->races()->count();
        $winsCount = $team->wins()->count();
        $podiumsCount = $team->podiums()->count();

        $team = [
            'id' => $team->id,
            'name' => $team->name,
            'nationality' => $team->nationality,
            'status' => $team->status,
            'races' => $racesCount,
            'wins' => $winsCount,
            'seasons' => $team->seasons()->count(),
            'championshipsCount' => $team->championships()?->count() ?? 0,
            'points' => $team->lastPoints(),
            'maxPoints' => $team->pointsHistory()->max('points'),
            'info' => [
                'firstRace' => $team->races()->first(),
                'lastRace' => $team->races()->last(),
                'firstWin' => $team->wins()->first(),
                'lastWin' => $team->wins()->last(),
                'winPercentage' => $racesCount > 0
                    ? round($winsCount / $racesCount * 100, 2)
                    : null,
                'podiums' => $podiumsCount,
                'podiumPercentage' => $racesCount > 0
                    ? round($podiumsCount / $racesCount * 100, 2)
                    : null,
                'withoutPosition' => $team->participations()
                    ->where('position', null)
                    ->count(),
                'raking' => Participation::teamsRanking()->firstWhere('team_id', $team->id),
                'championships' => $team->championships(),
            ],
            'pointsHistory' => $team->pointsHistory(),
            'positionsHistory' => $team->countForPosition(),
            'drivers' => $team->drivers(),
        ];

        return Inertia::render('teams/show', ['team' => $team]);
    }

    public function create()
    {
        return Inertia::render('teams/create');
    }

    public function store(StoreRequest $req)
    {
        $team = Team::create($req->validated());
        return redirect()->route('teams.show', $team->id);
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
        return redirect()->route('teams.show', $team->id);
    }

    public function destroy(string $id)
    {
        Team::findOrFail($id)->delete();
        return back();
    }
}
