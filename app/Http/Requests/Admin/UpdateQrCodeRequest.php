<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQrCodeRequest extends FormRequest
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
        $qrCodeId = $this->route('record') ?? $this->route('id');

        return [
            'code' => 'required|string|max:255|unique:qr_codes,code,' . $qrCodeId,
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'points_awarded' => 'required|integer|min:1',
            'start_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:start_at',
            'is_active' => 'boolean',
            'per_customer_limit' => 'nullable|integer|min:1',
            'total_limit' => 'nullable|integer|min:1',
        ];
    }
}
