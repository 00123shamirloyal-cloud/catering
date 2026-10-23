<?php

namespace Database\Factories;

use App\Models\FinancialAdjustment;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialAdjustment>
 */
class FinancialAdjustmentFactory extends Factory
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
            'type' => 'refund',
            'amount' => '25.00',
            'currency' => 'USD',
            'reason' => fake()->sentence(),
            'status' => 'recorded',
        ];
    }
}
