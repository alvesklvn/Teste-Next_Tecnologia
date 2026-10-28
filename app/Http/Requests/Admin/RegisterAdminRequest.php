<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterAdminRequest extends FormRequest
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
            'name' => "required|string|min:2|max:100",
            'email' => 'required|string|email:rfc,dns|unique:users,email',
            'password' => ['required', Password::min(8)->symbols()->numbers()->letters()->max(30)],
            'is_barber' => 'required|boolean',
            'phone' => 'required_if_accepted:is_barber|string|regex:/^[1-9]{2}9[0-9]{8}$/',
        ];
    }
}
