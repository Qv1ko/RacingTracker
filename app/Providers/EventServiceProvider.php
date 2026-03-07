<?php

namespace App\Providers;

use App\Events\RaceResultCalculated;
use App\Listeners\Calculations\TrueSkillCalculation;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register the correct calculation listener based on RANKING_ALGORITHM env var.
     */
    public function boot(): void
    {
        $listenerClass = config('ranking.algorithms')[config('ranking.algorithm')]
            ?? TrueSkillCalculation::class;

        $this->app['events']->listen(RaceResultCalculated::class, $listenerClass);
    }
}
