<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'INV-'.now()->format('Ymd').'-'.strtoupper(fake()->unique()->bothify('????????')),
            'customer_id' => Customer::factory(),
            'currency' => 'USD',
            'issued_at' => today(),
            'subtotal' => '1000.00',
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'total' => '1000.00',
            'status' => 'unpaid',
        ];
    }
}
