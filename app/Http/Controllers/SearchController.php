<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Race;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([
                'drivers' => [],
                'teams' => [],
                'races' => [],
                'seasons' => [],
            ]);
        }

        $like = '%'.mb_strtolower($term).'%';

        $drivers = Driver::query()
            ->where(function ($query) use ($like) {
                $query
                    ->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(surname) LIKE ?', [$like]);
            })
            ->orderByRaw('LOWER(surname) asc')
            ->limit(8)
            ->get(['id', 'name', 'surname', 'nationality'])
            ->map(fn (Driver $driver) => [
                'id' => $driver->id,
                'name' => $driver->name.' '.$driver->surname,
                'nationality' => $driver->nationality,
                'href' => '/drivers/'.$driver->id,
            ]);

        $teams = Team::query()
            ->whereRaw('LOWER(name) LIKE ?', [$like])
            ->orderByRaw('LOWER(name) asc')
            ->limit(8)
            ->get(['id', 'name', 'nationality'])
            ->map(fn (Team $team) => [
                'id' => $team->id,
                'name' => $team->name,
                'nationality' => $team->nationality,
                'href' => '/teams/'.$team->id,
            ]);

        $races = Race::query()
            ->whereRaw('LOWER(name) LIKE ?', [$like])
            ->orderByDesc('date')
            ->limit(8)
            ->get(['id', 'name', 'date'])
            ->map(fn (Race $race) => [
                'id' => $race->id,
                'name' => $race->name,
                'date' => $race->date,
                'href' => '/races/'.$race->id,
            ]);

        $seasons = Race::seasons()
            ->filter(fn (string $year) => str_contains((string) $year, $term))
            ->take(8)
            ->map(fn (string $year) => [
                'year' => $year,
                'href' => '/seasons/'.$year,
            ])
            ->values();

        return response()->json([
            'drivers' => $drivers,
            'teams' => $teams,
            'races' => $races,
            'seasons' => $seasons,
        ]);
    }
}
