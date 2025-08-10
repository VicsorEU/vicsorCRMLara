<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:191'],
            'email'    => ['required', 'email:rfc,dns', 'max:191', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:32', 'unique:users,phone', 'regex:/^\+?[0-9()\-\s]+$/'],
            'company'  => ['nullable', 'string', 'max:191'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Фамилия Имя',
            'email' => 'Почта',
            'phone' => 'Телефон',
            'company' => 'Компания',
            'password' => 'Пароль',
        ];
    }
}
