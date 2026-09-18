<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'graded_by' => User::where('role', 'dosen')->inRandomOrder()->first()?->id ?? User::factory()->dosen(),
            'score' => fake()->randomFloat(2, 50, 100),
            'feedback' => fake()->optional(0.7)->sentence(),
            'graded_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}