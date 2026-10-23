<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveStaffUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'owner' && $this->user()->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $staff = $this->route('staff');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff?->id)],
            'role' => ['required', Rule::in(['owner', 'manager', 'sales', 'operations', 'finance', 'content_editor'])],
            'password' => [$staff ? 'nullable' : 'required', 'string', 'min:12', 'confirmed'],
            'is_active' => [$staff ? 'required' : 'nullable', 'boolean'],
        ];
    }
}
