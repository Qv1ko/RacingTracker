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
                'Afghan',
                'Albanian',
                'Algerian',
                'Andorran',
                'Angolan',
                'Antiguan and barbudan',
                'Argentine',
                'Armenian',
                'Australian',
                'Austrian',
                'Azerbaijani',
                'Bahamian',
                'Bahraini',
                'Bangladeshi',
                'Barbadian',
                'Belarusian',
                'Belgian',
                'Belizean',
                'Beninese',
                'Bhutanese',
                'Bolivian',
                'Bosnian',
                'Botswanan',
                'Brazilian',
                'Bruneian',
                'Bulgarian',
                'Burkinabe',
                'Burundian',
                'Cambodian',
                'Cameroonian',
                'Canadian',
                'Cape Verdean',
                'Central African',
                'Chadian',
                'Chilean',
                'Chinese',
                'Colombian',
                'Comorian',
                'Congolese',
                'Costa Rican',
                'Ivorian',
                'Croatian',
                'Cuban',
                'Cypriot',
                'Czech',
                'Danish',
                'Djiboutian',
                'Dominican',
                'R_Dominican',
                'DR_Congolese',
                'Ecuadorian',
                'Egyptian',
                'Salvadoran',
                'Equatorial Guinean',
                'Eritrean',
                'Estonian',
                'Swazi',
                'Ethiopian',
                'Fijian',
                'Finnish',
                'French',
                'Gabonese',
                'Gambian',
                'Georgian',
                'German',
                'Ghanaian',
                'Greek',
                'Grenadian',
                'Guatemalan',
                'Bissau‑Guinean',
                'Guinean',
                'Guyanese',
                'Haitian',
                'Honduran',
                'Hungarian',
                'Icelander',
                'Indian',
                'Indonesian',
                'Iranian',
                'Iraqi',
                'Irish',
                'Israeli',
                'Italian',
                'Jamaican',
                'Japanese',
                'Jordanian',
                'Kazakhstani',
                'Kenyan',
                'I‑Kiribati',
                'Kuwaiti',
                'Kyrgyzstani',
                'Laotian',
                'Latvian',
                'Lebanese',
                'Mosotho',
                'Liberian',
                'Libyan',
                'Liechtensteiner',
                'Lithuanian',
                'Luxembourger',
                'Malagasy',
                'Malawian',
                'Malaysian',
                'Maldivian',
                'Malian',
                'Maltese',
                'Marshallese',
                'Mauritanian',
                'Mauritian',
                'Mexican',
                'Micronesian',
                'Moldovan',
                'Monégasque',
                'Mongolian',
                'Montenegrin',
                'Moroccan',
                'Mozambican',
                'Burmese',
                'Namibian',
                'Nauruan',
                'Nepalese',
                'Dutch',
                'New Zealander',
                'Nicaraguan',
                'Nigerien',
                'Nigerian',
                'North Korean',
                'North Macedonian',
                'Norwegian',
                'Omani',
                'Pakistani',
                'Palauan',
                'Palestinian',
                'Panamanian',
                'Papua New Guinean',
                'Paraguayan',
                'Peruvian',
                'Filipino',
                'Polish',
                'Portuguese',
                'Qatari',
                'Romanian',
                'Russian',
                'Rwandan',
                'Saint Kittian and Nevisian',
                'Saint Lucian',
                'Samoan',
                'San Marinese',
                'Sao Tomean',
                'Saudi Arabian',
                'Senegalese',
                'Serbian',
                'Seychellois',
                'Sierra Leonean',
                'Singaporean',
                'Slovakian',
                'Slovenian',
                'Solomon Islander',
                'Somali',
                'South African',
                'South Korean',
                'South Sudanese',
                'Spanish',
                'Sri Lankan',
                'Saint Vincentian',
                'Sudanese',
                'Surinamese',
                'Swedish',
                'Swiss',
                'Syrian',
                'Tajikistani',
                'Tanzanian',
                'Thai',
                'Timorese',
                'Togolese',
                'Tongan',
                'Trinidadian and Tobagonian',
                'Tunisian',
                'Turkish',
                'Turkmen',
                'Tuvaluan',
                'Ugandan',
                'Ukrainian',
                'Emirati',
                'British',
                'American',
                'Uruguayan',
                'Uzbekistani',
                'Ni-Vanuatu',
                'Venezuelan',
                'Vietnamese',
                'Yemeni',
                'Zambian',
                'Zimbabwean',
                null,
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
                // 1 de cada 3 usa un cluster
                $name .= $this->faker->randomElement($clusters)
                    . $this->faker->randomElement($vowels);
            } else {
                // consonante + vocal
                $name .= $this->faker->randomElement($consonants)
                    . $this->faker->randomElement($vowels);
            }
        }

        if ($this->faker->boolean) {
            $name .= $this->faker->randomElement($suffixes);
        }

        return ucfirst($name);
    }
}
