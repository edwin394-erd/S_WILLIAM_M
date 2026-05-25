<?php

namespace Database\Factories;

use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    public function definition(): array
    {
        return [
            'work_sheet_id' => \App\Models\WorkSheet::factory(),
            'odm_number' => fake()->unique()->numerify('6006013002####'),
            'type' => fake()->randomElement(['MANTENIMIENTO', 'CORRECTIVO', 'PREVENTIVO']),
            'impacto' => fake()->numberBetween(10, 120),
            'accion_requerida' => fake()->sentence(5),
            'installation_id' => \App\Models\Installation::factory(),
            'equipment_id' => \App\Models\Equipment::factory(),
            'is_high_risk' => fake()->boolean(15),
            'is_extraplan' => fake()->boolean(10),
            'comentario' => fake()->optional(0.5)->sentence(),
        ];
    }
}
