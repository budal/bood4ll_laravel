<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class UnitSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Unit::factory(30)
            ->sequence(fn (Sequence $sequence) => [
                'name' => ($sequence->index + 1) . " BPM",
                'parent_id' => 0
            ])
            ->create();

        // for($i=1; $i<=30; $i++) {
        //     \App\Models\Unit::factory()->create([
        //         'name' => $i ." BPM",
        //         'founded' => fake()->date(),
        //         'parent_id' => 0,
        //         'cellphone' => fake()->cellphoneNumber(),
        //         'landline' => fake()->landlineNumber(),
        //         'address' => fake()->address(),
        //         'country' => fake()->countryCode(),
        //         'postcode' => fake()->postcode(),
        //         'geo' => fake()->latitude() . "," . fake()->longitude(),
        //         'active' => true,
        //     ]);

        //     $unit_id = \App\Models\Unit::where('name', $i ." BPM")->first()->id;

        //     $collection = collect([
        //         "Comando",
        //         "Subomando",
        //         "P/1",
        //         "P/2",
        //         "P/3",
        //         "P/4",
        //         "P/5",
        //         "P/6",
        //     ]);

        //     $collection->map(function (string $item, int $key) use ($unit_id) {
        //         \App\Models\Unit::factory()->create([
        //             'name' => $item,
        //             'founded' => fake()->date(),
        //             'parent_id' => $unit_id,
        //             'cellphone' => fake()->cellphoneNumber(),
        //             'landline' => fake()->landlineNumber(),
        //             'address' => fake()->address(),
        //             'country' => fake()->countryCode(),
        //             'postcode' => fake()->postcode(),
        //             'geo' => fake()->latitude() . "," . fake()->longitude(),
        //             'active' => true,
        //         ]);
        //     });

        //     for($ii=1; $ii<=rand(3, 6); $ii++) {
        //         \App\Models\Unit::factory()->create([
        //             'name' => $ii ."a Cia",
        //             'founded' => fake()->date(),
        //             'parent_id' => $unit_id,
        //             'cellphone' => fake()->cellphoneNumber(),
        //             'landline' => fake()->landlineNumber(),
        //             'address' => fake()->address(),
        //             'country' => fake()->countryCode(),
        //             'postcode' => fake()->postcode(),
        //             'geo' => fake()->latitude() . "," . fake()->longitude(),
        //             'active' => true,
        //         ]);
        //     }
        // }
    }
}
