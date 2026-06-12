<?php

namespace Database\Factories;

use App\Models\OrderTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderTaskFactory extends Factory
{
    protected $model = OrderTask::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-6 months', 'now');
        $end = (clone $start)->modify('+3 hours');

        return [
            'work_order_id' => \App\Models\WorkOrder::factory(),
            'department_id' => \App\Models\Department::factory(),
            'discipline_id' => \App\Models\Discipline::factory(),
            'date' => $start->format('Y-m-d'),
            'time_start' => $start->format('Y-m-d H:i:s'),
            'time_end' => $end->format('Y-m-d H:i:s'),
            'status' => fake()->randomElement(['PENDIENTE', 'POR REVISION', 'COMPLETADO', 'NO COMPLETADO']),
            'priority' => fake()->randomElement(['Nivel 1', 'Nivel 2', 'Prioridad alta', 'Actividad critica']),
            'observation' => fake()->optional(0.6)->sentence(),
            'evidence_path' => null,
            'user_report_id' => null,
        ];
    }
}
