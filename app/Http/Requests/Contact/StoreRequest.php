<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'first_name'=>['required','string','max:191'],
            'last_name' =>['nullable','string','max:191'],
            'email'     =>['nullable','email','max:191'],
            'phone'     =>['nullable','string','max:32'],
            'position'  =>['nullable','string','max:191'],
            'company_id'=>['nullable','exists:companies,id'],
            'notes'     =>['nullable','string'],
        ];
    }
}
