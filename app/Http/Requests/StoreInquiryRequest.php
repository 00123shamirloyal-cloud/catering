<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInquiryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:7', 'max:40'],
            'preferred_contact' => ['required', Rule::in(['phone', 'email', 'whatsapp'])],
            'event_type' => ['required', 'string', 'max:100'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'alternative_date' => ['nullable', 'date', 'after_or_equal:today', 'different:event_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:50000'],
            'city' => ['required', 'string', 'max:120'],
            'venue' => ['nullable', 'string', 'max:255'],
            'setting' => ['nullable', Rule::in(['indoor', 'outdoor', 'unsure'])],
            'budget_range' => ['nullable', 'string', 'max:80'],
            'service_ids' => ['nullable', 'array', 'max:12'],
            'service_ids.*' => [
                'integer', 'distinct',
                Rule::exists('content_entries', 'id')->where(fn (Builder $query) => $query->where('type', 'service')->where('status', 'published')),
            ],
            'package_id' => [
                'nullable', 'integer',
                Rule::exists('content_entries', 'id')->where(fn (Builder $query) => $query->where('type', 'package')->where('status', 'published')),
            ],
            'dietary_preferences' => ['nullable', 'string', 'max:2000'],
            'requirements' => ['nullable', 'string', 'max:3000'],
            'message' => ['nullable', 'string', 'max:3000'],
            'privacy_accepted' => ['accepted'],
            'idempotency_token' => ['required', 'uuid'],
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'privacy_accepted.accepted' => 'Please agree to the privacy notice before sending your inquiry.',
            'event_date.after_or_equal' => 'Choose today or a future date for your event.',
            'end_time.after' => 'The end time must be later than the start time.',
        ];
    }
}
