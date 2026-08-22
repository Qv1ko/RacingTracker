<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\SyncRaceParticipations;
use App\Models\Race;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateRankings extends Command
{
    protected $signature = 'ranking:recalculate
        {--season= : Recalculate only the given season (year)}
        {--from= : Recalculate races dated on or after this date}';

    protected $description = 'Reset ratings to neutral values and replay every race result';

    public function handle(SyncRaceParticipations $sync): int
    {
        $races = Race::query()
            ->when($this->option('season'), fn ($q) => $q->whereYear('date', $this->option('season')))
            ->when($this->option('from'), fn ($q) => $q->where('date', '>=', $this->option('from')))
            ->orderBy('date')
            ->get();

        if ($races->isEmpty()) {
            $this->warn('No races found for the given filters.');

            return self::SUCCESS;
        }

        $participationCount = DB::table('participations')
            ->whereIn('race_id', $races->modelKeys())
            ->count();

        $this->info(sprintf(
            'Recalculating %s participations across %s races (%s)...',
            number_format($participationCount),
            number_format($races->count()),
            config('ranking.algorithm'),
        ));

        $sync->resetToNeutral($races);

        $bar = $this->output->createProgressBar($races->count());
        $bar->start();

        foreach ($races as $race) {
            $sync->replayRace($race);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Rankings recalculated successfully.');

        return self::SUCCESS;
    }
}
