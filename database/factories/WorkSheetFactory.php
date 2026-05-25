<?php

namespace Database\Factories;

use App\Models\WorkSheet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkSheetFactory extends Factory
{
    protected $model = WorkSheet::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-6 months', 'now');
        $end = (clone $start)->modify('+6 days');

        return [
            'week_number' => (int) $start->format('W'),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'total_odm_scheduled' => fake()->numberBetween(4, 12),
            'department_id' => \App\Models\Department::factory(),
            'codigo' => strtoupper(fake()->bothify('WS-??###')),
            'enviado' => fake()->boolean(70),
        ];
    }
}
