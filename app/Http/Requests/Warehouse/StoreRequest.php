<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'  => ['required','string','max:191'],
            'code'  => ['required','string','max:64','regex:/^[a-z0-9_-]+$/','unique:warehouses,code'],
            'description' => ['nullable','string'],
            'parent_id'   => ['nullable','exists:warehouses,id'],
            'manager_id'  => ['nullable','exists:users,id'],
            'phone'       => ['nullable','string','max:50'],

            'country'=>['nullable','string','max:191'],
            'region' =>['nullable','string','max:191'],
            'city'   =>['nullable','string','max:191'],
            'street' =>['nullable','string','max:191'],
            'house'  =>['nullable','string','max:50'],
            'postal_code'=>['nullable','string','max:20'],

            'is_active'            => ['sometimes','boolean'],
            'allow_negative_stock' => ['sometimes','boolean'],
            'sort_order'           => ['nullable','integer'],
        ];
    }
}
