<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = \App\Models\User::first();

        \App\Models\Calendar::factory()->afterCreating(function (\App\Models\Calendar $calendar) {
            $holidays = [
                ['name' => 'Confraternização Universal', 'day' => '01', 'month' => '01', 'start' => '00:00', 'end' => '23:59'],
                ['name' => 'Carnaval', 'easter' => true, 'diference_start' => '-48 days', 'diference_end' => '-46 days 23 hours 59 minutes'],
                ['name' => 'Quarta-Feira de Cinzas', 'easter' => true, 'diference_start' => '-46 days', 'diference_end' => '-46 days 12 hours'],
                ['name' => 'Sexta-Feira Santa', 'easter' => true, 'diference_start' => '-2 days', 'diference_end' => '-1 days 23 hours 59 minutes'],
                ['name' => 'Páscoa', 'easter' => true],
                ['name' => 'Corpus Christi', 'easter' => true, 'diference_start' => '+60 days', 'diference_end' => '+60 days 23 hours 59 minutes'],
                ['name' => 'Tiradentes', 'day' => '04', 'month' => '21', 'start' => '00:00', 'end' => '23:59'],
                ['name' => 'Dia mundial do Trabalho', 'day' => '01', 'month' => '05', 'start' => '00:00', 'end' => '23:59'],
                ['name' => 'Independência do Brasil', 'day' => '07', 'month' => '09', 'start' => '00:00', 'end' => '23:59'],
                ['name' => 'Dia de N. Sra. Aparecida', 'day' => '12', 'month' => '10', 'start' => '00:00', 'end' => '23:59'],
                ['name' => 'Dia do Servidor Público', 'day' => '28', 'month' => '10', 'start' => '00:00', 'end' => '23:59'],
                ['name' => 'Finados', 'day' => '02', 'month' => '11', 'start' => '00:00', 'end' => '23:59'],
                ['name' => 'Proclamação da República', 'day' => '15', 'month' => '11', 'start' => '00:00', 'end' => '23:59'],
                ['name' => 'Dia Nacional de Zumbi e da Consciência Negra', 'day' => '20', 'month' => '11', 'start' => '00:00', 'end' => '23:59'],
                ['name' => 'Natal', 'day' => '25', 'month' => '12', 'start' => '00:00', 'end' => '23:59'],
            ];

            \App\Models\Holiday::factory(count($holidays))
                ->state(new Sequence(...$holidays))->afterCreating(function (\App\Models\Holiday $holiday) use ($calendar) {
                    $calendar->holidays()->attach($holiday->id);
                })->create();
        })->create([
            'name' => 'Nacional',
            'owner' => $superAdmin->id,
            'active' => true,
            'year' => date("Y"),
        ]);
    }
}
