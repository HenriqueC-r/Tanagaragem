<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('state')) {
            $this->merge(['state' => strtoupper((string) $this->input('state'))]);
        }
    }

    public function rules(): array
    {
        $maxYear = (int) date('Y') + 1;

        return [
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'version' => ['nullable', 'string', 'max:150'],
            'year_manufacture' => ['required', 'integer', 'between:1900,' . $maxYear],
            'year_model' => ['required', 'integer', 'between:1900,' . $maxYear, 'gte:year_manufacture'],
            'mileage' => ['required', 'integer', 'between:0,2000000'],
            'fuel' => ['required', Rule::in(Vehicle::FUELS)],
            'transmission' => ['required', Rule::in(Vehicle::TRANSMISSIONS)],
            'color' => ['nullable', 'string', 'max:50'],
            'price_cents' => ['required', 'integer', 'between:100,500000000'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'alpha', 'size:2'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
