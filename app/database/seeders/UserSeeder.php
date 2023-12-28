<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        function classify($unit, $userId)
        {
            if ($unit->users()->where('name', 'ilike', '%comando%')->count() === 0) {
                $unit->users()->attach($userId);
            } else {
                $otherUnit = \App\Models\Unit::where('name', 'not ilike', '%comando%')->inRandomOrder()->first();
                $otherUnit->users()->attach($userId);
            }
        }

        \App\Models\User::factory()->create([
            'name' => 'Thiago Philipe Budal',
            'username' => 'budal.thiago',
            'email' => 'budal.thiago@gmail.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ]);

        \App\Models\User::factory(rand(18000, 20000))->afterCreating(function (\App\Models\User $user) {
            $unit = \App\Models\Unit::inRandomOrder()->first();

            $unit->users()->attach($user->id);

            // classify($unit, $user->id);
        })->create();
    }
}
