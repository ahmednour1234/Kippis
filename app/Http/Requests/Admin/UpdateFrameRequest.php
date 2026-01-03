<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFrameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by Filament
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name_json' => 'sometimes|array',
            'name_json.en' => 'required_with:name_json|string|max:255',
            'name_json.ar' => 'nullable|string|max:255',
            'thumbnail_path' => 'nullable|image|max:2048',
            'overlay_path' => 'sometimes|image|mimes:png|max:5120', // Only required if updating
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name_json.en.required_with' => 'The English name is required when updating name.',
            'overlay_path.mimes' => 'The overlay must be a PNG file.',
            'overlay_path.max' => 'The overlay must not be larger than 5MB.',
            'ends_at.after' => 'The end date must be after the start date.',
        ];
    }
}
