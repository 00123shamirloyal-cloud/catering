<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => '100.00',
            'currency' => 'USD',
            'method' => 'bank_transfer',
            'paid_at' => now(),
            'status' => 'recorded',
            'recorded_by' => User::factory()->state(['role' => 'finance']),
        ];
    }
}
