<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'created_by' => User::where('role', 'dosen')->inRandomOrder()->first()?->id ?? User::factory()->dosen(),
            'title' => 'Tugas ' . fake()->words(3, true),
            'instructions' => fake()->paragraphs(2, true),
            'due_at' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'max_score' => 100,
            'allow_late' => fake()->boolean(80),
            'status' => 'published',
        ];
    }
}