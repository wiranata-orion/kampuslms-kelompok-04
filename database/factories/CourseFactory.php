<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('SI25140##'),
            'name' => fake()->randomElement([
                'Pemrograman Web', 'Basis Data', 'Sistem Informasi Management',
                'Analisis Sistem', 'Jaringan Komputer', 'Keamanan Informasi'
            ]),
            'description' => fake()->paragraph(),
            'sks' => fake()->numberBetween(2, 4),
            'lecturer_id' => User::where('role', 'dosen')->inRandomOrder()->first()?->id ?? User::factory()->dosen(),
            'status' => fake()->randomElement(['draft', 'active', 'archived']),
        ];
    }
}