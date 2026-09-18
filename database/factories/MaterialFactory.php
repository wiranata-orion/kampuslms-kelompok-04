<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['file', 'link']);

        return [
            'course_id' => Course::factory(),
            'uploaded_by' => User::where('role', 'dosen')->inRandomOrder()->first()?->id ?? User::factory()->dosen(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'type' => $type,
            'file_path' => $type === 'file' ? 'materials/' . fake()->uuid() . '.pdf' : null,
            'original_name' => $type === 'file' ? fake()->word() . '.pdf' : null,
            'file_size' => $type === 'file' ? fake()->numberBetween(100000, 5000000) : null,
            'mime_type' => $type === 'file' ? 'application/pdf' : null,
            'external_url' => $type === 'link' ? fake()->url() : null,
        ];
    }
}