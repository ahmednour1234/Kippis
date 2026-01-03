<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class FramesRenderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by auth middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'frame_id' => 'required|integer|exists:frames,id',
            'image' => 'required|file|mimes:jpeg,jpg,png|max:10240', // 10MB
            'output_size' => 'nullable|string|regex:/^\d+x\d+$/',
            'format' => 'nullable|string|in:jpg,png',
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
            'frame_id.required' => 'The frame ID is required.',
            'frame_id.exists' => 'The selected frame does not exist.',
            'image.required' => 'The image file is required.',
            'image.file' => 'The image must be a valid file.',
            'image.mimes' => 'The image must be a JPEG, JPG, or PNG file.',
            'image.max' => 'The image must not be larger than 10MB.',
            'output_size.regex' => 'The output size must be in the format WIDTHxHEIGHT (e.g., 1080x1080).',
            'format.in' => 'The format must be either jpg or png.',
        ];
    }
}
