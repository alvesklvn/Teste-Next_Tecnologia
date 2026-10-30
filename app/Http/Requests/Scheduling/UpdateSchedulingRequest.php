<?php

namespace App\Http\Requests\Scheduling;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSchedulingRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'required_without_all:end_date,barber_id|date_format:Y-m-d H:i:s|after:now',
            'end_date' => 'required_without_all:start_date,barber_id|date_format:Y-m-d H:i:s|after:start_date',
            'barber_id' => 'required_without_all:start_date,end_date|integer|exists:barbers,id'
        ];
    }
}
