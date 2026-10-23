<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'type' => 'consultation',
            'starts_at' => now()->addDays(3)->setTime(14, 0),
            'ends_at' => now()->addDays(3)->setTime(15, 0),
            'timezone' => config('app.timezone'),
            'assigned_to' => User::factory()->state(['role' => 'manager']),
            'status' => 'scheduled',
        ];
    }
}
