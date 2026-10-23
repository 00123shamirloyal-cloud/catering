<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'revision_group' => (string) Str::uuid(),
            'revision_number' => 1,
            'currency' => 'USD',
            'subtotal' => '1000.00',
            'discount_amount' => '0.00',
            'charges_amount' => '0.00',
            'tax_rate' => '0.00',
            'tax_amount' => '0.00',
            'total' => '1000.00',
            'status' => 'draft',
        ];
    }
}
