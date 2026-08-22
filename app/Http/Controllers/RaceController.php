<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SyncRaceParticipations;
use App\Http\Requests\Race\StoreRequest;
use App\Http\Requests\Race\UpdateRequest;
use App\Models\Driver;
use App\Models\Race;
use App\Models\Team;
use App\Services\RacePresenter;
use App\Services\RankingService;
use Inertia\Inertia;

class RaceController extends Controller
{
    public function __construct(
        private SyncRaceParticipations $syncParticipations,
        private RankingService $ranking,
        private RacePresenter $presenter,
    ) {}

    public function index()
    {
        $seasons = Race::seasons();
        $season = request()->query('season');

        if ($season !== 'all' && ! in_array($season, $seasons->all())) {
            $season = $seasons->first() ?? 'all';
        }

        $races = Race::query()
            ->with(['participations.driver', 'participations.team'])
            ->when(
                $season === 'all',
                fn ($query) => $query->orderBy('name', 'asc'),
                fn ($query) => $query->inSeason($season)->orderBy('date', 'asc'),
            )
            ->get();

        $data = $races->map(fn (Race $race) => $this->presenter->present($race));

        return Inertia::render('races/index', [
            'seasons' => $seasons,
            'season' => $season,
            'races' => $data,
        ]);
    }

    public function show(Race $race)
    {
        $data = [
            'id' => $race->id,
            'name' => $race->name,
            'date' => $race->date,
            'result' => $this->ranking->raceResult($race),
            'driverStandings' => $this->ranking->raceDriverStandings($race),
            'teamStandings' => $this->ranking->raceTeamStandings($race),
            'more' => $race->getMore()
                ->map(fn (Race $otherRace) => $this->presenter->present($otherRace)),
        ];

        return Inertia::render('races/show', ['race' => $data]);
    }

    public function create()
    {
        return Inertia::render('races/create', [
            'drivers' => Driver::where('status', true)->get(),
            'teams' => Team::where('status', true)->get(),
        ]);
    }

    public function store(StoreRequest $req)
    {
        $race = Race::create($req->validated());

        $this->syncParticipations->handle($race, $req->result);

        return redirect()->route('races.show', $race->id);
    }

    public function edit(Race $race)
    {
        return Inertia::render('races/edit', [
            'race' => $race,
            'participations' => $race->participations,
            'drivers' => Driver::where('status', true)->get(),
            'teams' => Team::where('status', true)->get(),
        ]);
    }

    public function update(UpdateRequest $req, Race $race)
    {
        $race->update($req->validated());

        $this->syncParticipations->handle($race, $req->result);

        return redirect()->route('races.show', $race->id);
    }

    public function destroy(Race $race)
    {
        $date = $race->date;

        $race->delete();

        $this->syncParticipations->recalculateFrom($date);

        return back();
    }
}
