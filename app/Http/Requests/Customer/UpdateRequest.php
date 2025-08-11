<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends StoreRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'full_name' => ['required','string','max:191'],
            'manager_id'=> ['nullable','exists:users,id'],
            'note'      => ['nullable','string'],
            'birth_date'=> ['nullable','date'],

            // массив телефонов и почт
            'phones'        => ['array'],
            'phones.*'      => ['nullable','string','max:50'],
            'emails'        => ['array'],
            'emails.*'      => ['nullable','email','max:191'],

            // адрес (как было)
            'addr.country'     => ['nullable','string','max:191'],
            'addr.region'      => ['nullable','string','max:191'],
            'addr.city'        => ['nullable','string','max:191'],
            'addr.street'      => ['nullable','string','max:191'],
            'addr.house'       => ['nullable','string','max:50'],
            'addr.apartment'   => ['nullable','string','max:50'],
            'addr.postal_code' => ['nullable','string','max:20'],

            // каналы
            'channels'         => ['array'],
            'channels.*.kind'  => ['required','in:telegram,viber,whatsapp,instagram,facebook'],
            'channels.*.value' => ['required','string','max:191'],
        ];
    }


    public function attributes(): array
    {
        return ['full_name'=>'Полное имя','birth_date'=>'Дата рождения'];
    }
}
