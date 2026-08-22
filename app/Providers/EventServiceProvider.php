<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\RaceResultCalculated;
use App\Listeners\Calculations\TrueSkillCalculation;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $listenerClass = config('ranking.algorithms.'.config('ranking.algorithm'))
            ?? TrueSkillCalculation::class;

        $this->app['events']->listen(RaceResultCalculated::class, $listenerClass);
    }
}
