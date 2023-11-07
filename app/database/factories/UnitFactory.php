<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Units>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'founded' => fake()->date(),
            'parent_id' => fake()->randomNumber(1, false),
            'cellphone' => fake()->cellphoneNumber(),
            'landline' => fake()->landlineNumber(),
            'address' => fake()->address(),
            'country' => fake()->countryCode(),
            'postcode' => fake()->postcode(),
            'geo' => fake()->latitude() . "," . fake()->longitude(),
            'active' => fake()->boolean(),
        ];
    }
}
