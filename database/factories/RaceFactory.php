<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Race;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Race>
 */
class RaceFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->country().' GP  ',
            'date' => fake()->dateTimeThisYear()->format('Y-m-d').'T00:00:00.000Z',
        ];
    }

    public function fromYear(int $year): static
    {
        return $this->state(fn () => [
            'name' => fake()->country().' GP ',
            'date' => fake()->dateTimeBetween("$year-01-01 00:00:00", "$year-12-31 23:59:59")
                ->format('Y-m-d').'T00:00:00.000Z',
        ]);
    }
}
