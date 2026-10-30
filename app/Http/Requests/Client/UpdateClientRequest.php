<?php

namespace App\Http\Requests\Client;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateClientRequest extends FormRequest
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
            'name' => "required_without_all:password,phone,cep|string|min:2|max:100",
            'password' => ['required_without_all:name,phone,cep', Password::min(8)->symbols()->numbers()->letters()->max(30)],
            'phone' => 'required_without_all:name,password,cep|string|regex:/^[1-9]{2}9[0-9]{8}$/',
            'cep' => 'required_without_all:name,password,phone|string|regex:/^\d{8}$/'
        ];
    }
}
