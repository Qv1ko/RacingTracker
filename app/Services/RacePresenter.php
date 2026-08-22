<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Participation;
use App\Models\Race;

class RacePresenter
{
    public function present(Race $race): array
    {
        $betterDriver = $race->betterDriver();
        $betterTeam = $race->betterTeam();

        return [
            'id' => $race->id,
            'name' => $race->name,
            'date' => $race->date,
            'winner' => $this->podiumPosition($race->podiumPosition(1)),
            'second' => $this->podiumPosition($race->podiumPosition(2)),
            'third' => $this->podiumPosition($race->podiumPosition(3)),
            'betterDriver' => $betterDriver ? [
                'id' => $betterDriver->driver->id,
                'name' => $betterDriver->driver->name,
                'surname' => $betterDriver->driver->surname,
                'nationality' => $betterDriver->driver->nationality,
                'team' => $betterDriver->team,
            ] : null,
            'betterTeam' => $betterTeam ? [
                'id' => $betterTeam->team->id,
                'name' => $betterTeam->team->name,
                'nationality' => $betterTeam->team->nationality,
            ] : null,
        ];
    }

    private function podiumPosition(?Participation $participation): ?array
    {
        if ( ! $participation?->driver) {
            return null;
        }

        return [
            'id' => $participation->driver->id,
            'name' => $participation->driver->name,
            'surname' => $participation->driver->surname,
            'nationality' => $participation->driver->nationality,
            'team' => $participation->team,
        ];
    }
}
