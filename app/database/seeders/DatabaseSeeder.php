<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Unit::factory(rand(50, 60))->create();

        \App\Models\Role::factory()->create([
            'name' => ':: SUPERADMIN ::',
            'description' => 'Superadmin role',
            'full_control' => true,
            'locked' => true,
            'all_units' => true,
        ]);
        
        \App\Models\User::factory()->create([
            'name' => 'Thiago Philipe Budal',
            'username' => 'budal.thiago',
            'email' => 'budal.thiago@gmail.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ]);

        $role = \App\Models\Role::where('name', ':: SUPERADMIN ::')->get()->first();
        $user = \App\Models\User::where('email', 'budal.thiago@gmail.com')->get()->first();

        \App\Models\RoleUser::factory()->create([
            'role_id' => $role->id,
            'user_id' => $user->id,
        ]);
        
        \App\Models\User::factory(rand(18000, 20000))->create();
    }
}
