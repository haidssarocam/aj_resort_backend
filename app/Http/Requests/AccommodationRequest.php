<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccommodationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow admin users to create/update accommodations
        return auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:cottage,room,tent',
            'description' => 'required|string',
            'capacity_min' => 'required|integer|min:1',
            'capacity_max' => 'required|integer|min:1|gte:capacity_min',
            'duration_hours' => 'required|integer|in:3,22',
            'price' => 'required|numeric|min:0',
            'available_units' => 'required|integer|min:0',
            'image_path' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
} 