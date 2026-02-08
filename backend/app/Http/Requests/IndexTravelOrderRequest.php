<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexTravelOrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:requested,approved,canceled'],
            'destination' => ['nullable', 'string', 'max:255'],

            'travel_from' => ['nullable', 'date'],
            'travel_to' => ['nullable', 'date'],

            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date'],
        ];
    }
}
