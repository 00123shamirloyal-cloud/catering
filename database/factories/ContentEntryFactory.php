<?php

namespace Database\Factories;

use App\Models\ContentEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentEntry>
 */
class ContentEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'service',
            'title' => fake()->unique()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'category' => fake()->word(),
            'excerpt' => fake()->sentence(),
            'body' => fake()->paragraphs(2, true),
            'metadata' => [],
            'status' => 'draft',
            'is_featured' => false,
            'is_demo' => false,
            'sort_order' => 0,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'published', 'published_at' => now()]);
    }
}
