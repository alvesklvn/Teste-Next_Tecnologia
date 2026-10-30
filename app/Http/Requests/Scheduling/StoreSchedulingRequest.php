<?php

namespace App\Http\Requests\Scheduling;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSchedulingRequest extends FormRequest
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
        $user = request()->user();
        if ($user->userType->title === "admin" && !$user->barber()->exists()){
            return [
                'start_date' => 'required|date_format:Y-m-d H:i:s|after:now',
                'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
                'barber_id' => 'required|integer|exists:barbers,id',
                'client_id' => 'required|integer|exists:clients,id'
            ];
        } else if ($user->userType->title === "admin" && $user->barber()->exists()){
            return [
                'start_date' => 'required|date_format:Y-m-d H:i:s|after:now',
                'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
                'barber_id' => 'sometimes|integer|exists:barbers,id',
                'client_id' => 'required|integer|exists:clients,id'
            ];
        }

        return [
            'start_date' => 'required|date_format:Y-m-d H:i:s|after:now',
            'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
            'barber_id' => 'required|integer|exists:barbers,id',
            'client_id' => 'prohibited'
        ];
    }
}
