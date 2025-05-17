<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Race>
 */
class RaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->country() . ' GP  ',
            'date' => fake()->dateTimeThisYear()->format('Y-m-d') . 'T00:00:00.000Z',
        ];
    }

    /**
     * Generates a random date within the given year.
     */
    public function fromYear(int $year): static
    {
        return $this->state(fn() => [
            'name' => fake()->country() . ' GP ',
            'date' => fake()->dateTimeBetween("$year-01-01 00:00:00", "$year-12-31 23:59:59")
                ->format('Y-m-d') . 'T00:00:00.000Z',
        ]);
    }
}
