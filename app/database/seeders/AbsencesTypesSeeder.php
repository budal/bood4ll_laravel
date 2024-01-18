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
            ['name' => 'Férias', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => true, 'max_duration' => 30, 'acquisition_period' => 365, 'working_days' => true],
            ['name' => 'Férias (escolares)', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 30, 'acquisition_period' => 0, 'working_days' => true],
            ['name' => 'Férias (operador de raio-X)', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 20, 'acquisition_period' => 182, 'working_days' => false],
            ['name' => 'Dispensa por conta das férias', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 10, 'acquisition_period' => 365, 'working_days' => true],
            ['name' => 'Licença para tratamento da própria saúde', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 730, 'acquisition_period' => 0, 'working_days' => false],
            ['name' => 'Licença para tratamento da própria saúde (em serviço)', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 1460, 'acquisition_period' => 0, 'working_days' => false],
            ['name' => 'Licença para tratamento da saúde de pessoa da família', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 730, 'acquisition_period' => 0, 'working_days' => false],
            ['name' => 'Licença para tratamento de interesses particulares', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 730, 'acquisition_period' => 730, 'working_days' => false],
            ['name' => 'Licença especial', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 180, 'acquisition_period' => 1825, 'working_days' => false],
            ['name' => 'Licença capacitação', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 90, 'acquisition_period' => 1825, 'working_days' => false],
            ['name' => 'Dispensa comum', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 15, 'acquisition_period' => 0, 'working_days' => false],
            ['name' => 'Dispensa gala', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 8, 'acquisition_period' => 0, 'working_days' => false],
            ['name' => 'Dispensa nojo', 'owner' => $superAdmin->id, 'active' => true, 'use_vacation_plan' => false, 'max_duration' => 8, 'acquisition_period' => 0, 'working_days' => false],
        ];

        \App\Models\AbsencesType::factory(count($absencesTypes))
            ->state(new Sequence(...$absencesTypes))
            ->create();
    }
}
