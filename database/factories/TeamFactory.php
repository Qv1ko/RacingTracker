<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->generateTeamName(),
            'nationality' => fake()->randomElement([
                'Argentine',
                'Australian',
                'Austrian',
                'Belgian',
                'Brazilian',
                'Bulgarian',
                'Canadian',
                'Chinese',
                'Danish',
                'Finnish',
                'French',
                'German',
                'Greek',
                'Hungarian',
                'Indian',
                'Indonesian',
                'Irish',
                'Italian',
                'Japanese',
                'Dutch',
                'New Zealander',
                'Norwegian',
                'Polish',
                'Portuguese',
                'Romanian',
                'Russian',
                'Spanish',
                'Swedish',
                'Swiss',
                'Emirati',
                'British',
                'American',
            ]),
            'status' => true,
        ];
    }

    protected function generateTeamName(): string
    {
        $consonants = ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'z'];
        $clusters = ['br', 'cr', 'dr', 'tr', 'st', 'fl', 'cl', 'gr', 'pr', 'sp'];
        $vowels = ['a', 'e', 'i', 'o', 'u', 'y'];
        $suffixes = ['ix', 'on', 'ia', 'us', 'ex', 'or', 'ax', 'is'];

        $syllableCount = $this->faker->numberBetween(2, 4);
        $name = '';

        for ($i = 0; $i < $syllableCount; $i++) {
            if ($this->faker->numberBetween(1, 3) === 1) {
                $name .= $this->faker->randomElement($clusters).$this->faker->randomElement($vowels);
            } else {
                $name .= $this->faker->randomElement($consonants).$this->faker->randomElement($vowels);
            }
        }

        if ($this->faker->boolean) {
            $name .= $this->faker->randomElement($suffixes);
        }

        return ucfirst($name);
    }
}
