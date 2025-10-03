<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name'  => ['required','string','max:191'],
            'email' => ['nullable','email','max:191'],
            'phone' => ['nullable','string','max:32'],
            'website'=>['nullable','string','max:191'],
            'tax_number'=>['nullable','string','max:191'],
            'city'=>['nullable','string','max:191'],
            'country'=>['nullable','string','max:191'],
            'address'=>['nullable','string','max:191'],
            'notes'=>['nullable','string'],
        ];
    }
}
