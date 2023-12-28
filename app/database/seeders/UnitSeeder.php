<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Unit::factory()->afterCreating(function (\App\Models\Unit $unit) {
            $unit->fullpath = $unit->getParentsNames();
            $unit->shortpath = $unit->getParentsNicknames();

            $unit->save();
        })->create([
            'name' => 'PMPR',
            'nickname' => 'PMPR',
            'parent_id' => 0,
        ]);

        \App\Models\Unit::factory(6)
            ->sequence(fn (Sequence $sequence) => [
                'name' => ($sequence->index + 1) . ' CRPM',
                'nickname' => ($sequence->index + 1) . ' CRPM',
                'parent_id' => 1,
                'order' => $sequence->index + 1,
            ])->afterCreating(function (\App\Models\Unit $unit) {
                $unit->fullpath = $unit->getParentsNames();
                $unit->shortpath = $unit->getParentsNicknames();

                $unit->save();
            })->create();

        \App\Models\Unit::factory(30)
            ->sequence(fn (Sequence $sequence) => [
                'name' => ($sequence->index + 1) . ' BPM',
                'nickname' => ($sequence->index + 1) . ' BPM',
                'parent_id' => \App\Models\Unit::where('parent_id', 1)
                    ->inRandomOrder()
                    ->first()
                    ->id,
                'order' => $sequence->index + 1,
            ])->afterCreating(function (\App\Models\Unit $unit) {
                $unit->fullpath = $unit->getParentsNames();
                $unit->shortpath = $unit->getParentsNicknames();

                $unit->save();

                $order = 0;

                $subunits = [
                    ['name' => 'Comando', 'nickname' => 'Comando', 'parent_id' => $unit->id, 'order' => ++$order],
                    ['name' => 'Subcomando', 'nickname' => 'Subcomando', 'parent_id' => $unit->id, 'order' => ++$order],
                    ['name' => 'P/1', 'nickname' => 'P/1', 'parent_id' => $unit->id, 'order' => ++$order],
                    ['name' => 'P/2', 'nickname' => 'P/2', 'parent_id' => $unit->id, 'order' => ++$order],
                    ['name' => 'P/3', 'nickname' => 'P/3', 'parent_id' => $unit->id, 'order' => ++$order],
                    ['name' => 'P/4', 'nickname' => 'P/4', 'parent_id' => $unit->id, 'order' => ++$order],
                    ['name' => 'P/5', 'nickname' => 'P/5', 'parent_id' => $unit->id, 'order' => ++$order],
                    ['name' => 'P/6', 'nickname' => 'P/6', 'parent_id' => $unit->id, 'order' => ++$order],
                    ['name' => 'PCS', 'nickname' => 'PCS', 'parent_id' => $unit->id, 'order' => ++$order],
                    ['name' => 'ROTAM', 'nickname' => 'ROTAM', 'parent_id' => $unit->id, 'order' => ++$order],
                ];

                \App\Models\Unit::factory(count($subunits))
                    ->state(new Sequence(...$subunits))->afterCreating(function (\App\Models\Unit $unit) {
                        $unit->fullpath = $unit->getParentsNames();
                        $unit->shortpath = $unit->getParentsNicknames();

                        $unit->save();
                    })->create();

                \App\Models\Unit::factory(rand(3, 6))
                    ->sequence(fn (Sequence $sequence) => [
                        'name' => ($sequence->index + 1) . ' Cia',
                        'nickname' => ($sequence->index + 1) . ' Cia',
                        'parent_id' => $unit->id,
                        'order' => $sequence->index + ++$order,
                    ])->afterCreating(function (\App\Models\Unit $unit) {
                        $unit->fullpath = $unit->getParentsNames();
                        $unit->shortpath = $unit->getParentsNicknames();

                        $unit->save();
                    })->create();
            })->create();
    }
}
