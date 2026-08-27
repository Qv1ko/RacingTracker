<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Team\StoreRequest;
use App\Http\Requests\Team\UpdateRequest;
use App\Models\Driver;
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
            $season = $seasons->first() ?? 'all';
        }

        if ($season === 'all') {
            $teams = Team::with('drivers')->orderByRaw('LOWER(name) asc')->get();
            $driversByTeam = null;
        } else {
            $teams = Team::whereHas('participations.race', function ($query) use ($season) {
                $query->whereYear('date', $season);
            })
                ->orderByRaw('LOWER(name) asc')
                ->get();

            $teamDriverPairs = DB::table('participations as p')
                ->join('races as r', 'r.id', '=', 'p.race_id')
                ->whereYear('r.date', $season)
                ->whereNotNull('p.team_id')
                ->distinct()
                ->get(['p.team_id', 'p.driver_id']);

            $driversByTeam = $teamDriverPairs->groupBy('team_id');
        }

        $driversById = Driver::query()
            ->whereIn('id', $season === 'all'
                ? []
                : $teamDriverPairs->pluck('driver_id')->unique())
            ->get()
            ->keyBy('id');

        $data = $teams->map(function ($team) use ($season, $driversByTeam, $driversById) {
            return [
                'id' => $team->id,
                'name' => $team->name,
                'nationality' => $team->nationality,
                'status' => $team->status,
                'color' => $team->color,
                'drivers' => $driversByTeam === null
                    ? $team->drivers->unique('id')->values()
                    : collect($driversByTeam->get($team->id, []))
                        ->map(fn ($pair) => $driversById->get($pair->driver_id))
                        ->filter()
                        ->values(),
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

        $racesCount = $team->races()->count();
        $winsCount = $team->stats()->getPositionsCount();
        $podiumsCount = $team->stats()->getPodiums()->count();

        $team = [
            'id' => $team->id,
            'name' => $team->name,
            'nationality' => $team->nationality,
            'status' => $team->status,
            'color' => $team->color,
            'races' => $racesCount,
            'wins' => $winsCount,
            'seasons' => $teamStats->seasons()->count(),
            'championshipsCount' => $teamStats->championships()->count(),
            'points' => $teamStats->lastPoints(),
            'maxPoints' => $teamStats->pointsHistory()->max('points'),
            'activity' => $teamStats->activity(),
            'info' => [
                'firstRace' => $team->races()->orderBy('races.date')->first(),
                'lastRace' => $team->races()->orderByDesc('races.date')->first(),
                'firstWin' => $team->races()->where('position', 1)->first(),
                'lastWin' => $team->races()->where('position', 1)->orderByDesc('races.date')->first(),
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
                'ranking' => $this->formatRankingPosition($ranking->teamPosition($team)),
                'championships' => $teamStats->championships(),
            ],
            'pointsHistory' => $teamStats->pointsHistory(),
            'drivers' => $team->drivers->unique('id')->values(),
        ];

        return Inertia::render('teams/show', ['team' => $team]);
    }

    private function formatRankingPosition(?int $position): ?array
    {
        return $position !== null ? ['position' => $position] : null;
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
