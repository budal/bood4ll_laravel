<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->userName() . Str::random(3),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'active' => fake()->boolean(),
            'confirmed' => fake()->boolean(),
            'birthday' => fake()->date(),
            'naturalness' => fake()->city() . "," . fake()->stateAbbr(),
            'cellphone' => fake()->cellphoneNumber(),
            'landline' => fake()->landlineNumber(),
            'address' => fake()->address(),
            'country' => fake()->countryCode(),
            'geo' => fake()->latitude() . "," . fake()->longitude(),
            'postcode' => fake()->postcode(),
            'gerenal_record' => fake()->unique()->bothify('#.###.###-#'),
            'individual_registration' => fake()->bothify('###.###.###-##'),
            'driver_licence' => fake()->regexify('[0-9]{16}'),
            'voter_registration' => fake()->regexify('[0-9]{16}'),
            'social_security_card' => fake()->regexify('[0-9]{16}'),
            'passaport_number' => Str::upper(Str::random(16)),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
