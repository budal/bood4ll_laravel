<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'nickname' => fake()->company(),
            'parent_id' => fake()->randomNumber(1, false),
            'founded' => fake()->date(),
            'expires' => fake()->dateTimeBetween('-1 year', '+3 month'),
            'active' => fake()->boolean(),
            'cellphone' => fake()->cellphoneNumber(),
            'landline' => fake()->landlineNumber(),
            'email' => fake()->unique()->safeEmail(),
            'country' => fake()->countryCode(),
            'state' => fake()->stateAbbr(),
            'city' => fake()->city(),
            'address' => fake()->streetName() . ', ' . fake()->buildingNumber(),
            'complement' => fake()->secondaryAddress(),
            'postcode' => fake()->postcode(),
            'geo' => fake()->latitude() . "," . fake()->longitude(),
        ];
    }
}
