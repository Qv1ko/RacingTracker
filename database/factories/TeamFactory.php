<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
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
                'American'
            ]),
            'status' => true,
        ];
    }

    /**
     * Generates a 2-4 syllable team name + optional suffix.
     */
    protected function generateTeamName(): string
    {
        $consonants = ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'z'];
        $clusters   = ['br', 'cr', 'dr', 'tr', 'st', 'fl', 'cl', 'gr', 'pr', 'sp'];
        $vowels     = ['a', 'e', 'i', 'o', 'u', 'y'];
        $suffixes   = ['ix', 'on', 'ia', 'us', 'ex', 'or', 'ax', 'is'];

        $syllableCount = $this->faker->numberBetween(2, 4);
        $name = '';

        for ($i = 0; $i < $syllableCount; $i++) {
            if ($this->faker->numberBetween(1, 3) === 1) {
                $name .= $this->faker->randomElement($clusters) . $this->faker->randomElement($vowels);
            } else {
                $name .= $this->faker->randomElement($consonants) . $this->faker->randomElement($vowels);
            }
        }

        if ($this->faker->boolean) {
            $name .= $this->faker->randomElement($suffixes);
        }

        return ucfirst($name);
    }
}
