<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class SuperadminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = \App\Models\User::first();

        $superAdmin->roles()->attach(\App\Models\Role::first()->id);
    }
}
