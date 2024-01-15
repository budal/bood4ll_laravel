<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class AbsencesTypesSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = \App\Models\User::first();

        $absencesTypes = [
            ['name' => 'Confraternização Universal', 'owner' => $superAdmin->id, 'active' => true, 'duration' => 5, 'working_days' => true],
        ];

        \App\Models\Holiday::factory(count($absencesTypes))
            ->state(new Sequence(...$absencesTypes))
            ->create();
    }
}
