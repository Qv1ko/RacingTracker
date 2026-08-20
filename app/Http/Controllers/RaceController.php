<?php

namespace App\Http\Controllers;

use App\Events\RaceResultCalculated;
use App\Http\Requests\Race\StoreRequest;
use App\Http\Requests\Race\UpdateRequest;
use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RaceController extends Controller
{
    public function index(Request $req)
    {
        $seasons = Race::seasons();
        $season = $req->query('season');

        if ($season !== 'all' && ! in_array($season, $seasons->all())) {
            $season = Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)")) ?? 'all';
        }

        if ($season === 'all') {
            $races = Race::orderBy('name', 'asc')->get();
        } else {
            $races = Race::whereYear('date', $season)
                ->orderBy('date', 'asc')
                ->get();
        }

        $data = $races->map(function ($race) {
            return [
                'id' => $race->id,
                'name' => $race->name,
                'date' => $race->date,
                'winner' => $race->participant(1),
                'second' => $race->participant(2),
                'third' => $race->participant(3),
                'better' => $race->better(),
            ];
        });

        return Inertia::render('races/index', [
            'seasons' => $seasons,
            'season' => $season,
            'races' => $data,
        ]);
    }

    public function show(string $id)
    {
        $race = Race::findOrFail($id);

        $more = $race->more();

        $data = [
            'id' => $race->id,
            'name' => $race->name,
            'date' => $race->date,
            'result' => Participation::raceResult($race->id),
            'driverStandings' => Participation::raceDriverStandings($race->id),
            'teamStandings' => Participation::raceTeamStandings($race->id),
            'more' => $more->map(function ($race) {
                return [
                    'id' => $race->id,
                    'name' => $race->name,
                    'date' => $race->date,
                    'winner' => $race->participant(1),
                    'second' => $race->participant(2),
                    'third' => $race->participant(3),
                    'better' => $race->better(),
                ];
            }),
        ];

        return Inertia::render('races/show', ['race' => $data]);
    }

    public function create()
    {
        $drivers = Driver::where('status', true)->get();
        $teams = Team::where('status', true)->get();

        return Inertia::render('races/create', ['drivers' => $drivers, 'teams' => $teams]);
    }

    public function store(StoreRequest $req)
    {
        $race = Race::create($req->validated());

        foreach ($req->result as $participation) {
            [$previousPoints, $previousUncertainty] = $this->previousRating($participation['driver'], $race);

            Participation::create([
                'driver_id' => $participation['driver'],
                'team_id' => $participation['team'],
                'race_id' => $race->id,
                'position' => intval($participation['position']) ? intval($participation['position']) : null,
                'status' => $participation['position'],
                'points' => $previousPoints,
                'uncertainty' => $previousUncertainty,
            ]);
        }

        $this->recalculateFrom($race);

        return redirect()->route('races.show', $race->id);
    }

    public function edit(Race $race)
    {
        $drivers = Driver::where('status', true)->get();
        $teams = Team::where('status', true)->get();

        return Inertia::render('races/edit', [
            'race' => $race,
            'participations' => $race->participations,
            'drivers' => $drivers,
            'teams' => $teams,
        ]);
    }

    public function update(UpdateRequest $req, $id)
    {
        $race = Race::findOrFail($id);
        $race->update($req->validated());

        $race->participations()->delete();

        foreach ($req->result as $participationData) {
            [$previousPoints, $previousUncertainty] = $this->previousRating($participationData['driver'], $race);

            Participation::create([
                'driver_id' => $participationData['driver'],
                'team_id' => $participationData['team'],
                'race_id' => $race->id,
                'position' => intval($participationData['position']) ? intval($participationData['position']) : null,
                'status' => $participationData['position'],
                'points' => $previousPoints,
                'uncertainty' => $previousUncertainty,
            ]);
        }

        $this->recalculateFrom($race);

        return redirect()->route('races.show', $race->id);
    }

    public function destroy(string $id)
    {
        $race = Race::findOrFail($id);

        $race->delete();

        $this->recalculateFrom($race);

        return back();
    }

    private function previousRating(int|string $driverId, Race $race): array
    {
        $latest = Participation::where('driver_id', $driverId)
            ->whereHas('race', fn ($query) => $query->where('date', '<', $race->date))
            ->orderByDesc(Race::select('date')
                ->whereColumn('races.id', 'participations.race_id')
                ->limit(1))
            ->first();

        return [
            $latest?->points ?? config('ranking.defaults.mu'),
            $latest?->uncertainty ?? config('ranking.defaults.sigma'),
        ];
    }

    private function recalculateFrom(Race $race): void
    {
        Participation::whereHas('race', fn ($query) => $query->where('date', '>=', $race->date))
            ->with('race')
            ->get()
            ->sortBy(fn ($participation) => $participation->race->date)
            ->groupBy('race_id')
            ->each(fn ($participations) => RaceResultCalculated::dispatch($participations));
    }
}
