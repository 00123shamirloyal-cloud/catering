<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\Inquiry;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->canManageAppointments() ?? false;
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
            'type' => ['required', 'string', Rule::in(config('catering.appointment_types'))],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'inquiry_id' => ['nullable', 'integer', 'exists:inquiries,id'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'starts_at' => ['required', 'date', 'after_or_equal:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'timezone' => ['nullable', 'timezone:all'],
            'assigned_to' => [
                'required', 'integer',
                Rule::exists('users', 'id')->where(fn (Builder $query) => $query->where('is_active', true)->whereIn('role', ['owner', 'manager', 'sales', 'operations'])),
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'reminder_at' => ['nullable', 'date', 'before:starts_at'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['customer_id', 'inquiry_id', 'event_id'])) {
                return;
            }

            $customerId = $this->integer('customer_id') ?: null;
            $inquiry = $this->filled('inquiry_id') ? Inquiry::query()->find($this->integer('inquiry_id')) : null;
            $event = $this->filled('event_id') ? Event::query()->find($this->integer('event_id')) : null;

            if ($inquiry && $customerId && $inquiry->customer_id !== $customerId) {
                $validator->errors()->add('customer_id', 'Choose the customer linked to this inquiry.');
            }

            if ($event && $customerId && $event->customer_id !== $customerId) {
                $validator->errors()->add('customer_id', 'Choose the customer linked to this event.');
            }

            if ($event && $inquiry && $event->inquiry_id !== $inquiry->id) {
                $validator->errors()->add('event_id', 'The selected event is not linked to this inquiry.');
            }
        }];
    }
}
