<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveContentEntryRequest extends FormRequest
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
        $entryId = $this->route('contentEntry')?->id;
        $type = $this->input('type', $this->route('contentEntry')?->type);

        return [
            'type' => ['required', Rule::in(['page', 'service', 'menu_item', 'package', 'portfolio', 'testimonial', 'faq', 'banner'])],
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'required', 'string', 'max:190', 'alpha_dash',
                Rule::unique('content_entries', 'slug')->where('type', $type)->ignore($entryId),
            ],
            'category' => ['nullable', 'string', 'max:100'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:30000'],
            'metadata' => ['nullable', 'array'],
            'metadata.price_label' => ['nullable', 'string', 'max:100'],
            'metadata.price_model' => ['nullable', Rule::in(['per_person', 'fixed', 'quotation'])],
            'metadata.minimum_guests' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'metadata.alt_text' => ['nullable', 'string', 'max:180'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
