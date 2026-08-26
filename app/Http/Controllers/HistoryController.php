<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $ranking = new RankingService;

        return Inertia::render('history', [
            'drivers' => $ranking->driversRanking(),
            'teams' => $ranking->teamsRanking(),
        ]);
    }
}
