<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Participation;
use Illuminate\Console\Command;

class PointsNormalize extends Command
{
    protected $signature = 'points:normalize';

    protected $description = 'Collapse same-race duplicate participations so a driver\'s cumulative points never decrease';

    public function handle(): int
    {
        $drivers = Participation::pluck('driver_id')->unique();
        $updated = 0;

        foreach ($drivers as $driverId) {
            $participations = Participation::where('driver_id', $driverId)
                ->with('race')
                ->orderBy('race_id')
                ->get();

            $grouped = $participations->groupBy(fn ($p) => $p->race->date);

            foreach ($grouped as $raceDate => $raceRows) {
                $maxPoints = $raceRows->max('points');

                foreach ($raceRows as $participation) {
                    if ($participation->points < $maxPoints) {
                        $participation->points = $maxPoints;
                        $participation->save();
                        $updated++;
                    }
                }
            }
        }

        $this->info("Done. {$updated} participations updated.");

        return self::SUCCESS;
    }
}
