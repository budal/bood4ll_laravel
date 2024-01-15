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
            ['name' => 'Férias', 'owner' => $superAdmin->id, 'active' => true, 'duration' => 30, 'working_days' => false],
            ['name' => 'Férias (operador de raio-X)', 'owner' => $superAdmin->id, 'active' => true, 'duration' => 20, 'working_days' => false],
            ['name' => 'Dispensa por conta das férias', 'owner' => $superAdmin->id, 'active' => true, 'duration' => 30, 'working_days' => false],
            ['name' => 'Licença para tratamento da própria saúde', 'owner' => $superAdmin->id, 'active' => true, 'duration' => 670, 'working_days' => true],
            ['name' => 'Licença para tratamento da saúde de pessoa da família', 'owner' => $superAdmin->id, 'active' => true, 'duration' => 670, 'working_days' => true],
            ['name' => 'Licença para tratamento de interesses particulares', 'owner' => $superAdmin->id, 'active' => true, 'duration' => 670, 'working_days' => true],
            ['name' => 'Licença especial', 'owner' => $superAdmin->id, 'active' => true, 'duration' => 0, 'working_days' => true],
            ['name' => 'Licença capacitação', 'owner' => $superAdmin->id, 'active' => true, 'duration' => 0, 'working_days' => true],
        ];

        \App\Models\AbsencesType::factory(count($absencesTypes))
            ->state(new Sequence(...$absencesTypes))
            ->create();
    }
}
