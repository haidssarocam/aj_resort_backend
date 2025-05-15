<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'accommodation_id' => 'required|exists:accommodations,id',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string|in:credit_card,cash,bank_transfer,gcash',
        ];

        // If this is an update and the user is admin, allow status updates
        if ($this->isMethod('PUT') && auth()->user()->isAdmin()) {
            $rules['status'] = 'sometimes|string|in:pending,confirmed,completed,cancelled';
        }

        return $rules;
    }
} 