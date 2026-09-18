<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'user_id' => User::where('role', 'mahasiswa')->inRandomOrder()->first()?->id ?? User::factory(),
            'file_path' => 'submissions/' . fake()->uuid() . '.pdf',
            'original_name' => 'Tugas_' . fake()->word() . '.pdf',
            'file_size' => fake()->numberBetween(200000, 8000000),
            'note' => fake()->optional(0.4)->sentence(),
            'submitted_at' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'is_late' => fake()->boolean(20),
        ];
    }
}