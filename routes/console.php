<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('f1:sync')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('ranking:recalculate')
    ->weeklyOn(1, '05:00')
    ->withoutOverlapping()
    ->onOneServer();
