<?php

namespace App\Contracts;

use App\Events\RaceResultCalculated;

interface RatingCalculation
{
    public function handle(RaceResultCalculated $event): void;
}
