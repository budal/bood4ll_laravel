<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'Thiago Philipe Budal',
            'username' => 'budal.thiago',
            'email' => 'budal.thiago@gmail.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ]);

        \App\Models\User::factory(rand(18000, 20000))->afterCreating(function (\App\Models\User $user) {
            $unit = \App\Models\Unit::inRandomOrder()->first();

            $unit->users()->attach($user->id);
    })->create();
    }
}
