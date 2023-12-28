<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Role::factory()->create([
            'name' => '::SUPERADMIN::',
            'description' => 'Superadmin role',
            'inalterable' => true,
            'superadmin' => true,
            'manager' => true,
            'active' => true,
        ]);

        \App\Models\Role::factory()->create([
            'name' => '::ADMIN::',
            'description' => 'Admin role',
            'inalterable' => true,
            'manager' => true,
            'active' => true,
        ]);
    }
}
