<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['owner', 'manager', 'sales', 'finance'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'inquiry_id' => ['nullable', 'integer', 'exists:inquiries,id'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'revision_of' => ['nullable', 'integer', 'exists:quotations,id'],
            'currency' => ['required', 'string', 'regex:/^[A-Z]{3}$/'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.description' => ['required', 'string', 'max:240'],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:100000'],
            'items.*.unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100000000'],
            'discount_amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'charges_amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'deposit_percent' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'exclusions' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
