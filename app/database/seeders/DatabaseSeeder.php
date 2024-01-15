<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UnitSeeder::class,
            UserSeeder::class,
            SuperadminUserSeeder::class,
            CalendarSeeder::class,
            AbsencesTypesSeeder::class,
            VacationPlansSeeder::class,
        ]);
    }
}
