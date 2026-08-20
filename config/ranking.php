<?php

use App\Listeners\Calculations\ClassicCalculation;
use App\Listeners\Calculations\PositionCalculation;
use App\Listeners\Calculations\TrueSkillCalculation;

return [

    /*
    |--------------------------------------------------------------------------
    | Rating Algorithm
    |--------------------------------------------------------------------------
    |
    | Set RANKING_ALGORITHM in your .env to choose which calculation listener
    | is registered for the RaceResultCalculated event.
    |
    | Available options: 'classic', 'position', 'trueskill'
    |
    */

    'algorithm' => env('RANKING_ALGORITHM') ?: 'trueskill',

    'algorithms' => [
        'classic' => ClassicCalculation::class,
        'position' => PositionCalculation::class,
        'trueskill' => TrueSkillCalculation::class,
    ],

    'defaults' => [
        'mu' => 25.0,
        'sigma' => 8.333, // mu / 3
    ],

    // F1 points table: position => points
    'classic_points' => [
        1 => 25,
        2 => 18,
        3 => 15,
        4 => 12,
        5 => 10,
        6 => 8,
        7 => 6,
        8 => 4,
        9 => 2,
        10 => 1,
    ],

];
