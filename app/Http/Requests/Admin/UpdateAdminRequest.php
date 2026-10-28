<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateAdminRequest extends FormRequest
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
        if (request()->method() === "PUT"){
            return [
                'name' => "required|string|min:2|max:100",
                'password' => ['required', Password::min(8)->symbols()->numbers()->letters()->max(30)],
                'is_barber' => 'required|boolean',
                'phone' => 'required_if_accepted:is_barber|string|regex:/^[1-9]{2}9[0-9]{8}$/',
            ];
        } else {
            return [
            'name' => "required_without_all:password,is_barber,phone|string|min:2|max:100",
            'password' => ['required_without_all:name,is_barber,phone', Password::min(8)->symbols()->numbers()->letters()->max(30)],
            'is_barber' => 'required_without_all:name,password,phone|boolean',
            'phone' => 'required_if_accepted:is_barber|required_without_all:name,password,is_barber|string|regex:/^[1-9]{2}9[0-9]{8}$/',
        ];
        }
    }
}
