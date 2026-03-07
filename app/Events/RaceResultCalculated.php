<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class RaceResultCalculated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  Collection  $participations  The participations of the race, already persisted with position/status.
     */
    public function __construct(public readonly Collection $participations) {}
}
