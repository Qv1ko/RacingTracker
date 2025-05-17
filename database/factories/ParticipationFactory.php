<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Driver;
use App\Models\Participation;
use App\Models\Race;
use App\Models\Team;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participation>
 */
class ParticipationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $race = Race::inRandomOrder()->first();
        $nextPosition = Participation::where('race_id', $race->id)
            ->whereNotNull('position')
            ->count() + 1;

        $driverId = Driver::where('status', true)
            ->whereNotIn('id', function ($query) use ($race) {
                $query->select('driver_id')
                    ->from('participations')
                    ->where('race_id', $race->id);
            })
            ->inRandomOrder()
            ->value('id');

        $lastParticipation = Participation::where('driver_id', $driverId)
            ->whereHas('race', function ($query) use ($race) {
                $query->where('date', '<', $race->date);
            })
            ->orderByDesc(Race::select('date')
                ->whereColumn('id', 'participations.race_id')
                ->limit(1))
            ->first();

        $lastTeamId = $lastParticipation?->team_id;

        $teamId = $lastTeamId ?? Team::where('status', true)->inRandomOrder()->value('id');

        if ($lastTeamId && fake()->boolean(1)) {
            $teamId = Team::where('status', true)
                ->where('id', '!=', $lastTeamId)
                ->inRandomOrder()
                ->value('id') ?? $lastTeamId;
        }

        $notFinishStatuses = ['DNF', 'DNQ', 'DNS', "DQ", 'EXC', 'NC', 'OTL', 'RET'];

        $points = $lastParticipation ? $lastParticipation->points : Participation::$MU;
        $uncertainty = $lastParticipation ? $lastParticipation->uncertainty : Participation::$SIGMA;

        return $this->buildRace(
            $race->id,
            $driverId,
            $teamId,
            $nextPosition,
            $notFinishStatuses,
            $points,
            $uncertainty,
        );
    }

    public function race(int $raceId): static
    {
        $race = Race::findOrFail($raceId);
        $nextPosition = Participation::where('race_id', $raceId)
            ->whereNotNull('position')
            ->count() + 1;

        $driverId = Driver::where('status', true)
            ->whereNotIn('id', function ($query) use ($raceId) {
                $query->select('driver_id')
                    ->from('participations')
                    ->where('race_id', $raceId);
            })
            ->inRandomOrder()
            ->value('id');

        $lastParticipation = Participation::where('driver_id', $driverId)
            ->whereHas('race', function ($query) use ($race) {
                $query->where('date', '<', $race->date);
            })
            ->orderByDesc(Race::select('date')
                ->whereColumn('id', 'participations.race_id')
                ->limit(1))
            ->first();

        $lastTeamId = $lastParticipation?->team_id;

        $teamId = $lastTeamId ?? Team::where('status', true)->inRandomOrder()->value('id');

        if ($lastTeamId && fake()->boolean(1)) {
            $teamId = Team::where('status', true)
                ->where('id', '!=', $lastTeamId)
                ->inRandomOrder()
                ->value('id') ?? $lastTeamId;
        }

        $notFinishStatuses = ['DNF', 'DNQ', 'DNS', "DQ", 'EXC', 'NC', 'OTL', 'RET'];

        $points = $lastParticipation ? $lastParticipation->points : Participation::$MU;
        $uncertainty = $lastParticipation ? $lastParticipation->uncertainty : Participation::$SIGMA;

        return $this->state(fn() => $this->buildRace(
            $raceId,
            $driverId,
            $teamId,
            $nextPosition,
            $notFinishStatuses,
            $points,
            $uncertainty,
        ));
    }

    private function buildRace(
        int $raceId,
        int $driverId,
        int | null $teamId,
        int $nextPosition,
        array $notFinishStatuses,
        float $points,
        float $uncertainty,
    ): array {
        if (fake()->boolean(5)) {
            return [
                'race_id'     => $raceId,
                'driver_id'   => $driverId,
                'team_id'     => $teamId,
                'position'    => null,
                'status'      => fake()->randomElement($notFinishStatuses),
                'points'      => $points,
                'uncertainty' => $uncertainty,
            ];
        }

        return [
            'race_id'     => $raceId,
            'driver_id'   => $driverId,
            'team_id'     => $teamId,
            'position'    => $nextPosition,
            'status'      => $nextPosition,
            'points'      => $points,
            'uncertainty' => $uncertainty,
        ];
    }
}
