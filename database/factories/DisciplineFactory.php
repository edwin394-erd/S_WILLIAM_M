<?php

namespace Database\Factories;

use App\Models\Discipline;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisciplineFactory extends Factory
{
    protected $model = Discipline::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
            'department_id' => \App\Models\Department::factory(),
        ];
    }
}
