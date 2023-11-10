<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Role::factory()->create([
            'name' => ':: SUPERADMIN ::',
            'description' => 'Superadmin role',
            'full_control' => true,
            'locked' => true,
            'all_units' => true,
        ]);
    }
}
