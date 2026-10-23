<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventFromInquiryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->canManageEvents() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:100'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'venue' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'event_manager_id' => [
                'nullable', 'integer',
                Rule::exists('users', 'id')->where(fn (Builder $query) => $query->whereIn('role', ['owner', 'manager', 'operations'])),
            ],
            'internal_notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
