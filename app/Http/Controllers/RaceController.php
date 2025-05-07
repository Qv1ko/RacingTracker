<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\Team;
use App\Models\Race;
use App\Http\Requests\Race\StoreRequest;
use App\Http\Requests\Race\UpdateRequest;
use App\Models\Participation;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class RaceController extends Controller
{
    public function index(Request $req)
    {
        $seasons = Race::seasons();
        $season = $req->query('season');

        if ($season !== 'all' && !in_array($season, $seasons->all())) {
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

        $data = [
            'id' => $race->id,
            'name' => $race->name,
            'date' => $race->date,
            'result' => Participation::raceResult($race->id),
            'driverStandings' => Participation::raceDriverStandings($race->id),
            'teamStandings' => Participation::raceTeamStandings($race->id),
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
            Participation::create([
                'driver_id' => $participation['driver'],
                'team_id' => $participation['team'],
                'race_id' => $race->id,
                'position' => intval($participation['position']) ? intval($participation['position']) : null,
                'status' => $participation['position'],
                'points' => Participation::where('driver_id', $participation['driver'])
                    ->whereHas('race', function ($query) use ($race) {
                        $query->where('date', '<', $race->date);
                    })
                    ->orderByDesc(Race::select('date')
                        ->whereColumn('id', 'participations.race_id')
                        ->limit(1))
                    ->first()
                    ?->points ?? Participation::$MU,
                'uncertainty' => Participation::where('driver_id', $participation['driver'])
                    ->whereHas('race', function ($query) use ($race) {
                        $query->where('date', '<', $race->date);
                    })
                    ->orderByDesc(Race::select('date')
                        ->whereColumn('id', 'participations.race_id')
                        ->limit(1))
                    ->first()
                    ?->uncertainty ?? Participation::$SIGMA,
            ]);
        }

        Participation::calcRaceResult(Participation::whereHas('race', function ($query) use ($race) {
            $query->where('date', '>=', $race->date);
        })->get());

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
            'teams' => $teams
        ]);
    }

    public function update(UpdateRequest $req, $id)
    {
        $race = Race::findOrFail($id);
        $race->update($req->validated());

        foreach ($req->result as $participationData) {
            $participation = Participation::where('driver_id', $participationData['driver'])
                ->where('race_id', $race->id);

            if ($participation) {
                $participation->update([
                    'team_id' => $participationData['team'],
                    'position' => intval($participationData['position']) ? intval($participationData['position']) : null,
                    'status' => $participationData['position'],
                    'points' => Participation::where('driver_id', $participationData['driver'])
                        ->whereHas('race', function ($query) use ($race) {
                            $query->where('date', '<', $race->date);
                        })
                        ->orderByDesc(Race::select('date')
                            ->whereColumn('id', 'participations.race_id')
                            ->limit(1))
                        ->first()
                        ?->points ?? Participation::$MU,
                    'uncertainty' => Participation::where('driver_id', $participationData['driver'])
                        ->whereHas('race', function ($query) use ($race) {
                            $query->where('date', '<', $race->date);
                        })
                        ->orderByDesc(Race::select('date')
                            ->whereColumn('id', 'participations.race_id')
                            ->limit(1))
                        ->first()
                        ?->uncertainty ?? Participation::$SIGMA,
                ]);
            } else {
                Participation::create([
                    'driver_id' => $participationData['driver'],
                    'team_id' => $participationData['team'],
                    'race_id' => $race->id,
                    'position' => intval($participationData['position']) ? intval($participationData['position']) : null,
                    'status' => $participationData['position'],
                    'points' => Participation::where('driver_id', $participationData['driver'])
                        ->whereHas('race', function ($query) use ($race) {
                            $query->where('date', '<', $race->date);
                        })
                        ->orderByDesc(Race::select('date')
                            ->whereColumn('id', 'participations.race_id')
                            ->limit(1))
                        ->first()
                        ?->points ?? Participation::$MU,
                    'uncertainty' => Participation::where('driver_id', $participationData['driver'])
                        ->whereHas('race', function ($query) use ($race) {
                            $query->where('date', '<', $race->date);
                        })
                        ->orderByDesc(Race::select('date')
                            ->whereColumn('id', 'participations.race_id')
                            ->limit(1))
                        ->first()
                        ?->uncertainty ?? Participation::$SIGMA,
                ]);
            }
        }

        Participation::calcRaceResult(Participation::whereHas('race', function ($query) use ($race) {
            $query->where('date', '>=', $race->date);
        })->get());

        return redirect()->route('races.show', $race->id);
    }

    public function destroy(string $id)
    {
        $race = Race::findOrFail($id);

        Participation::calcRaceResult(Participation::whereHas('race', function ($query) use ($race) {
            $query->where('date', '>=', $race->date);
        })->get());

        $race->delete();

        return back();
    }
}
