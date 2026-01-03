<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateFrameRequest extends FormRequest
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
            'name_json' => 'required|array',
            'name_json.en' => 'required|string|max:255',
            'name_json.ar' => 'nullable|string|max:255',
            'thumbnail_path' => 'nullable|image|max:2048',
            'overlay_path' => 'required|image|mimes:png|max:5120',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
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
            'name_json.required' => 'The name is required.',
            'name_json.en.required' => 'The English name is required.',
            'overlay_path.required' => 'The overlay PNG file is required.',
            'overlay_path.mimes' => 'The overlay must be a PNG file.',
            'overlay_path.max' => 'The overlay must not be larger than 5MB.',
            'ends_at.after' => 'The end date must be after the start date.',
        ];
    }
}
