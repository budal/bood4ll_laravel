<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class VacationPlansSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = \App\Models\User::first();

        $absencesTypes = [
            ['period' => 1, 'owner' => $superAdmin->id, 'year' => 2024, 'starts_at' => '2024-01-02', 'ends_at' => '2024-02-05', 'implantation_at' => '2023-12-01'],
            ['period' => 2, 'owner' => $superAdmin->id, 'year' => 2024, 'starts_at' => '2024-02-06', 'ends_at' => '2024-03-12', 'implantation_at' => '2024-01-01'],
            ['period' => 3, 'owner' => $superAdmin->id, 'year' => 2024, 'starts_at' => '2024-03-13', 'ends_at' => '2024-04-17', 'implantation_at' => '2024-02-01'],
            ['period' => 4, 'owner' => $superAdmin->id, 'year' => 2024, 'starts_at' => '2024-04-18', 'ends_at' => '2024-05-23', 'implantation_at' => '2024-03-01'],
            ['period' => 5, 'owner' => $superAdmin->id, 'year' => 2024, 'starts_at' => '2024-05-24', 'ends_at' => '2024-06-28', 'implantation_at' => '2024-05-01'],
            ['period' => 6, 'owner' => $superAdmin->id, 'year' => 2024, 'starts_at' => '2024-07-01', 'ends_at' => '2024-08-03', 'implantation_at' => '2024-06-01'],
            ['period' => 7, 'owner' => $superAdmin->id, 'year' => 2024, 'starts_at' => '2024-08-05', 'ends_at' => '2024-09-09', 'implantation_at' => '2024-07-01'],
            ['period' => 8, 'owner' => $superAdmin->id, 'year' => 2024, 'starts_at' => '2024-09-10', 'ends_at' => '2024-10-15', 'implantation_at' => '2024-08-01'],
            ['period' => 9, 'owner' => $superAdmin->id, 'year' => 2024, 'starts_at' => '2024-10-16', 'ends_at' => '2024-11-21', 'implantation_at' => '2024-09-01'],
            ['period' => 10, 'owner' => $superAdmin->id, 'year' => 2024, 'starts_at' => '2024-11-22', 'ends_at' => '2024-12-27', 'implantation_at' => '2024-11-01'],
        ];

        \App\Models\VacationPlan::factory(count($absencesTypes))
            ->state(new Sequence(...$absencesTypes))
            ->create();
    }
}
