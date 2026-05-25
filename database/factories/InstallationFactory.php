<?php

namespace Database\Factories;

use App\Models\Installation;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstallationFactory extends Factory
{
    protected $model = Installation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' ' . fake()->randomElement(['Planta', 'Sitio', 'Sector', 'Linea']),
            'impact' => fake()->numberBetween(10, 120),
        ];
    }
}
