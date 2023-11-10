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

        $role = \App\Models\Role::where('name', ':: SUPERADMIN ::')->get()->first();
        $user = \App\Models\User::where('email', 'budal.thiago@gmail.com')->get()->first();

        \App\Models\RoleUser::factory()->create([
            'role_id' => $role->id,
            'user_id' => $user->id,
        ]);
    }
}
