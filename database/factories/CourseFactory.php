<?php

namespace Database\Factories;

use App\Enums\CourseStatus;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Kelas '.fake()->unique()->words(3, true),
            'description' => fake()->paragraph(),
            'status' => CourseStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => CourseStatus::Published]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => CourseStatus::Draft]);
    }
}
