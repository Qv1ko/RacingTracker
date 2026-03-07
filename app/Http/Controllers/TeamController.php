<?php

namespace App\Http\Controllers;

use App\Http\Requests\Team\StoreRequest;
use App\Http\Requests\Team\UpdateRequest;
use App\Models\Race;
use App\Models\Team;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function index(Request $req)
    {
        $seasons = Race::seasons();
        $season = $req->query('season');

        if ($season !== 'all' && ! in_array($season, $seasons->all())) {
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
                'drivers' => $team->drivers(),
                'races' => $team->races()->count(),
                'wins' => $team->stats()->getPositionsCount($season),
                'second_positions' => $team->stats()->getPositionsCount($season, 2),
                'third_positions' => $team->stats()->getPositionsCount($season, 3),
                'points' => $team->stats()->lastPoints($season),
            ];
        });

        return Inertia::render('teams/index', [
            'seasons' => $seasons,
            'season' => $season,
            'teams' => $data,
        ]);
    }

    public function show(Team $team)
    {
        $ranking = new RankingService;
        $teamStats = $team->stats();

        $racesCount = $team->races->count();
        $winsCount = $team->stats()->getPositionsCount();
        $podiumsCount = $team->stats()->getPodiums()->count();

        $team = [
            'id' => $team->id,
            'name' => $team->name,
            'nationality' => $team->nationality,
            'status' => $team->status,
            'races' => $racesCount,
            'wins' => $winsCount,
            'seasons' => $teamStats->seasons()->count(),
            'championshipsCount' => $teamStats->championships()->count(),
            'points' => $teamStats->lastPoints(),
            'maxPoints' => $teamStats->pointsHistory()->max('points'),
            'info' => [
                'firstRace' => $team->races->first(),
                'lastRace' => $team->races->last(),
                'firstWin' => $team->races()->where('position', 1)->first(),
                'lastWin' => $team->races()->where('position', 1)->get()->last(),
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
                'ranking' => $ranking->teamsRanking()->firstWhere('team.id', $team->id),
                'championships' => $teamStats->championships(),
            ],
            'pointsHistory' => $teamStats->pointsHistory(),
            // 'positionsHistory' => $teamStats->countForPosition(),
            'drivers' => $team->drivers,
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
