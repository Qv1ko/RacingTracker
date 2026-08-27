<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Race;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CompareController extends Controller
{
    public function index(Request $request)
    {
        $seasons = Race::seasons();
        $validated = $request->validate([
            'season' => ['nullable', 'string', Rule::in($seasons->all())],
            'driver1' => ['nullable', 'integer'],
            'driver2' => ['nullable', 'integer'],
        ]);

        $season = $validated['season'] ?? $seasons->first();
        $drivers = $season === null
            ? collect()
            : Driver::query()
                ->whereHas('participations.race', fn ($query) => $query->whereYear('date', $season))
                ->orderByRaw('LOWER(surname) asc')
                ->get(['id', 'name', 'surname', 'nationality', 'color']);

        $firstId = $this->validSelection($validated['driver1'] ?? null, $drivers->pluck('id'));
        $secondId = $this->validSelection($validated['driver2'] ?? null, $drivers->pluck('id'));

        if ($firstId === null) {
            $firstId = $drivers->first()?->id;
        }
        if ($secondId === null || $secondId === $firstId) {
            $secondId = $drivers->first(fn (Driver $driver) => $driver->id !== $firstId)?->id;
        }

        $selectedIds = collect([$firstId, $secondId])->filter()->unique()->values();
        $summary = $season !== null && $selectedIds->isNotEmpty()
            ? DB::table('participations')
                ->join('races', 'races.id', '=', 'participations.race_id')
                ->whereYear('races.date', $season)
                ->whereIn('participations.driver_id', $selectedIds)
                ->groupBy('participations.driver_id')
                ->get([
                    'participations.driver_id',
                    DB::raw('COUNT(DISTINCT participations.race_id) as races'),
                    DB::raw('SUM(CASE WHEN participations.position = 1 THEN 1 ELSE 0 END) as wins'),
                    DB::raw('SUM(CASE WHEN participations.position BETWEEN 1 AND 3 THEN 1 ELSE 0 END) as podiums'),
                    DB::raw('AVG(CASE WHEN participations.position IS NOT NULL THEN participations.position END) as average_finish'),
                ])
                ->mapWithKeys(fn ($row) => [$row->driver_id => [
                    'races' => (int) $row->races,
                    'wins' => (int) $row->wins,
                    'podiums' => (int) $row->podiums,
                    'averageFinish' => $row->average_finish !== null ? (float) $row->average_finish : null,
                ]])
            : collect();

        $comparison = $selectedIds->map(function (int $id) use ($drivers, $season, $summary) {
            $driver = $drivers->firstWhere('id', $id);
            $stats = $driver->stats();

            return [
                'driver' => $driver,
                'summary' => array_merge($summary->get($id, [
                    'races' => 0,
                    'wins' => 0,
                    'podiums' => 0,
                    'averageFinish' => null,
                ]), [
                    'points' => $stats->lastPoints($season) ?? 0,
                ]),
                'pointsHistory' => $stats->pointsHistory($season)->values(),
            ];
        })->values();

        return Inertia::render('compare', [
            'seasons' => $seasons,
            'season' => $season,
            'drivers' => $drivers,
            'selected' => ['driver1' => $firstId, 'driver2' => $secondId],
            'comparison' => $comparison,
        ]);
    }

    private function validSelection(?int $id, $available): ?int
    {
        return $id !== null && $available->contains($id) ? $id : null;
    }
}
