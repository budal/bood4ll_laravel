<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class UnitSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Unit::factory()->create([
            'name' => '[ root ]',
            'nickname' => '[ root ]',
            'parent_id' => 0
        ]);

        \App\Models\Unit::factory(6)
            ->sequence(fn (Sequence $sequence) => [
                'name' => ($sequence->index + 1) . " CRPM",
                'nickname' => ($sequence->index + 1) . " CRPM",
                'parent_id' => 1
            ])->create();
        
        \App\Models\Unit::factory(30)
            ->sequence(fn (Sequence $sequence) => [
                'name' => ($sequence->index + 1) . " BPM",
                'nickname' => ($sequence->index + 1) . " BPM",
                'parent_id' => \App\Models\Unit::where('parent_id', 1)
                    ->inRandomOrder()
                    ->first()
                    ->id
            ])->afterCreating(function (\App\Models\Unit $unit) {
                \App\Models\Unit::factory(8)
                ->state(new Sequence(
                    ['name' => 'Comando', 'nickname' => 'Comando', 'parent_id' => $unit->id],
                    ['name' => 'Subcomando', 'nickname' => 'Subcomando', 'parent_id' => $unit->id],
                    ['name' => 'P/1', 'nickname' => 'P/1', 'parent_id' => $unit->id],
                    ['name' => 'P/2', 'nickname' => 'P/2', 'parent_id' => $unit->id],
                    ['name' => 'P/3', 'nickname' => 'P/3', 'parent_id' => $unit->id],
                    ['name' => 'P/4', 'nickname' => 'P/4', 'parent_id' => $unit->id],
                    ['name' => 'P/5', 'nickname' => 'P/5', 'parent_id' => $unit->id],
                    ['name' => 'P/6', 'nickname' => 'P/6', 'parent_id' => $unit->id],
                ))->create();

                \App\Models\Unit::factory(rand(3, 6))
                ->sequence(fn (Sequence $sequence) => [
                    'name' => ($sequence->index + 1) . " Cia",
                    'parent_id' => $unit->id
                ])->create();
            })->create();

    }
}
