<?php

namespace Database\Factories;

use App\Models\ContentEntry;
use App\Models\ContentEntryVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentEntryVersion>
 */
class ContentEntryVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content_entry_id' => ContentEntry::factory(),
            'version_number' => 1,
            'snapshot' => [
                'title' => fake()->sentence(4),
                'slug' => fake()->unique()->slug(),
                'category' => fake()->word(),
                'excerpt' => fake()->sentence(),
                'body' => fake()->paragraph(),
                'image_path' => null,
                'metadata' => [],
                'status' => 'draft',
                'is_featured' => false,
                'sort_order' => 0,
                'published_at' => null,
            ],
        ];
    }
}
