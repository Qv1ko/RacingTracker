<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Events\RaceResultCalculated;

interface RatingCalculation
{
    public function handle(RaceResultCalculated $event): void;
}
