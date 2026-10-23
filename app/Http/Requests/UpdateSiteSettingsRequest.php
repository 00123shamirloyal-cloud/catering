<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->canManageWebsite() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:180'],
            'hero_title' => ['required', 'string', 'max:180'],
            'hero_subtitle' => ['required', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'business_hours' => ['nullable', 'string', 'max:500'],
            'whatsapp_url' => ['nullable', 'url', 'max:255'],
            'currency_code' => ['nullable', 'string', 'regex:/^[A-Z]{3}$/'],
            'service_areas' => ['nullable', 'array', 'max:20'],
            'service_areas.*' => ['nullable', 'string', 'max:120'],
            'show_demo_notice' => ['nullable', 'boolean'],
            'demo_notice' => ['nullable', 'string', 'max:220'],
        ];
    }
}
