<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class SuperadminUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\RoleUser::factory()->create([
            'role_id' => \App\Models\Role::first()->id,
            'user_id' => \App\Models\User::first()->id,
        ]);
    }
}
