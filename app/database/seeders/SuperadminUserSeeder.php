<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SuperadminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = \App\Models\User::first();

        $superAdmin->roles()->attach(\App\Models\Role::first()->id);
    }
}
