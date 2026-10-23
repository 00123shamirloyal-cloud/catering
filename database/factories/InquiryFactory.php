<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => 'INQ-'.strtoupper(fake()->unique()->bothify('??????????')),
            'idempotency_token' => fake()->uuid(),
            'customer_id' => Customer::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->e164PhoneNumber(),
            'preferred_contact' => 'email',
            'event_type' => fake()->randomElement(['Wedding', 'Corporate event', 'Private celebration']),
            'event_date' => now()->addMonths(2)->toDateString(),
            'guest_count' => fake()->numberBetween(20, 300),
            'city' => fake()->city(),
            'privacy_accepted' => true,
            'status' => 'new',
            'submitted_at' => now(),
        ];
    }
}
