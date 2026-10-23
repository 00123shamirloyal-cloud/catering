<?php

namespace Database\Factories;

use App\Models\FollowUpTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FollowUpTask>
 */
class FollowUpTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'due_at' => now()->addDay(),
            'priority' => 'normal',
            'status' => 'open',
        ];
    }
}
