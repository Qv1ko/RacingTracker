<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\SyncF1Data;
use App\Actions\SyncRaceParticipations;
use Illuminate\Console\Command;

class F1Sync extends Command
{
    protected $signature = 'f1:sync
        {--from= : Import every season from the given year up to the current one}';

    protected $description = 'Import missing F1 seasons, races and results from the Jolpica API (existing data is never overwritten)';

    public function handle(SyncF1Data $sync, SyncRaceParticipations $ratings): int
    {
        $currentYear = (int) now()->format('Y');
        $from = (int) ($this->option('from') ?? $currentYear - 1);
        $seasons = range(max(1950, $from), $currentYear);

        if ($this->option('from') === null) {
            $this->info(sprintf('Importing missing data for seasons %d-%d...', $seasons[0], end($seasons)));
        } else {
            $this->info(sprintf('Importing full history from %d to %d...', $seasons[0], end($seasons)));
        }

        $affectedSeasons = $sync->syncSeasons($seasons);

        if ($affectedSeasons === []) {
            $this->info('Everything already up to date, nothing imported.');

            return self::SUCCESS;
        }

        $this->info('New data found in seasons: '.implode(', ', $affectedSeasons));

        $this->info('Recalculating ratings for affected seasons...');

        $ratings->recalculateSeasons($affectedSeasons);

        $this->info('Done.');

        return self::SUCCESS;
    }
}
