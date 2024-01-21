<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UsersInfoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'birthday' => fake()->date(),
            'naturalness' => fake()->city() . ',' . fake()->stateAbbr(),
            'cellphone' => fake()->cellphoneNumber(),
            'landline' => fake()->landlineNumber(),
            'address' => fake()->address(),
            'country' => fake()->countryCode(),
            'geo' => fake()->latitude() . ',' . fake()->longitude(),
            'postcode' => fake()->postcode(),
            'general_record' => fake()->unique()->bothify('#.###.###-#'),
            'individual_registration' => fake()->bothify('###.###.###-##'),
            'driver_licence' => fake()->regexify('[0-9]{16}'),
            'voter_registration' => fake()->regexify('[0-9]{16}'),
            'social_security_card' => fake()->regexify('[0-9]{16}'),
            'passaport_number' => Str::upper(Str::random(16)),
        ];
    }
}
