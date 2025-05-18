<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(),
            'nationality' => fake()->randomElement([
                'Argentine',
                'Australian',
                'Austrian',
                'Azerbaijani',
                'Belgian',
                'Brazilian',
                'Bulgarian',
                'Canadian',
                'Chinese',
                'Croatian',
                'Czech',
                'Danish',
                'Finnish',
                'French',
                'Georgian',
                'German',
                'Greek',
                'Hungarian',
                'Indonesian',
                'Irish',
                'Israeli',
                'Italian',
                'Japanese',
                'Laotian',
                'Latvian',
                'Lithuanian',
                'Luxembourger',
                'Mexican',
                'Monégasque',
                'Mongolian',
                'Montenegrin',
                'Moroccan',
                'Dutch',
                'New Zealander',
                'Paraguayan',
                'Polish',
                'Portuguese',
                'Romanian',
                'Russian',
                'Saudi Arabian',
                'Serbian',
                'Slovakian',
                'Slovenian',
                'South Korean',
                'Spanish',
                'Swedish',
                'Swiss',
                'Turkish',
                'Ukrainian',
                'Emirati',
                'British',
                'American',
                'Uruguayan',
                'Vietnamese',
                null,
            ]),
            'status' => true,
        ];
    }
}
