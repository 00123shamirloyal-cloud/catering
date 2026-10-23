<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFinancialAdjustmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['owner', 'manager', 'finance'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $invoice = $this->route('invoice');

        return [
            'type' => ['required', Rule::in(['refund', 'credit_note'])],
            'payment_id' => [
                'nullable',
                'required_if:type,refund',
                'integer',
                Rule::exists('payments', 'id')->where(fn (Builder $query) => $query->where('invoice_id', $invoice->id)),
            ],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:100000000'],
            'reference' => ['nullable', 'string', 'max:80'],
            'reason' => ['required', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('type') === 'credit_note' && $this->filled('payment_id')) {
                $validator->errors()->add('payment_id', 'A credit note is not linked to a payment.');
            }
        }];
    }
}
