<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => 'EVT-'.strtoupper(fake()->unique()->bothify('??????????')),
            'customer_id' => Customer::factory(),
            'title' => fake()->sentence(4),
            'category' => fake()->randomElement(['Wedding', 'Corporate', 'Private celebration']),
            'event_date' => now()->addMonths(2)->toDateString(),
            'start_time' => '18:00',
            'end_time' => '23:00',
            'venue' => fake()->streetAddress(),
            'city' => fake()->city(),
            'guest_count' => fake()->numberBetween(30, 300),
            'event_manager_id' => User::factory()->state(['role' => 'manager']),
            'status' => 'tentative_hold',
            'hold_expires_at' => now()->addHours(24),
        ];
    }
}
