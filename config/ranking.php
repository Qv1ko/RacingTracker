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

    'algorithm' => env('RANKING_ALGORITHM', 'trueskill'),

    'algorithms' => [
        'classic' => ClassicCalculation::class,
        'position' => PositionCalculation::class,
        'trueskill' => TrueSkillCalculation::class,
    ],

];
