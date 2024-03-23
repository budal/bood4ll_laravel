<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            ['name' => 'Confraternização Universal', 'authority' => 'federal', 'day_off' => true, 'day' => '01', 'month' => '01', 'start_time' => '00:00', 'end_time' => '23:59:59'],
            ['name' => 'Carnaval', 'authority' => 'federal', 'day_off' => true, 'easter' => true, 'operator' => '-', 'difference_start' => "50 days", 'difference_end' => "46 days 1 second"],
            ['name' => 'Quarta-Feira de Cinzas', 'authority' => 'federal', 'day_off' => true, 'easter' => true, 'operator' => '-', 'difference_start' => "46 days", 'difference_end' => "45 days 10 hours"],
            ['name' => 'Sexta-Feira Santa', 'authority' => 'federal', 'day_off' => true, 'easter' => true, 'operator' => '-', 'difference_start' => "2 days", 'difference_end' => "1 day 1 second"],
            ['name' => 'Páscoa', 'authority' => 'federal', 'day_off' => false, 'easter' => true, 'operator' => '+', 'difference_start' => "0 hours", 'difference_end' => "23 hours 59 minutes 59 seconds"],
            ['name' => 'Corpus Christi', 'authority' => 'federal', 'day_off' => true, 'easter' => true, 'operator' => '+', 'difference_start' => "60 days", 'difference_end' => "60 days 23 hours 59 minutes 59 seconds"],
            ['name' => 'Tiradentes', 'authority' => 'federal', 'day_off' => true, 'day' => '21', 'month' => '04', 'start_time' => '00:00', 'end_time' => '23:59:59'],
            ['name' => 'Dia mundial do Trabalho', 'authority' => 'federal', 'day_off' => true, 'day' => '01', 'month' => '05', 'start_time' => '00:00', 'end_time' => '23:59:59'],
            ['name' => 'Independência do Brasil', 'authority' => 'federal', 'day_off' => true, 'day' => '07', 'month' => '09', 'start_time' => '00:00', 'end_time' => '23:59:59'],
            ['name' => 'Dia de N. Sra. Aparecida', 'authority' => 'federal', 'day_off' => true, 'day' => '12', 'month' => '10', 'start_time' => '00:00', 'end_time' => '23:59:59'],
            ['name' => 'Dia do Servidor Público', 'authority' => 'federal', 'day_off' => true, 'day' => '28', 'month' => '10', 'start_time' => '00:00', 'end_time' => '23:59:59'],
            ['name' => 'Finados', 'authority' => 'federal', 'day_off' => true, 'day' => '02', 'month' => '11', 'start_time' => '00:00', 'end_time' => '23:59:59'],
            ['name' => 'Proclamação da República', 'authority' => 'federal', 'day_off' => true, 'day' => '15', 'month' => '11', 'start_time' => '00:00', 'end_time' => '23:59:59'],
            ['name' => 'Dia Nacional de Zumbi e da Consciência Negra', 'authority' => 'federal', 'day_off' => true, 'day' => '20', 'month' => '11', 'start_time' => '00:00', 'end_time' => '23:59:59'],
            ['name' => 'Natal', 'authority' => 'federal', 'day_off' => true, 'day' => '25', 'month' => '12', 'start_time' => '00:00', 'end_time' => '23:59:59'],
        ];

        \App\Models\Holiday::factory(count($holidays))
            ->state(new Sequence(...$holidays))
            ->create();
    }
}
