<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Validation\Rule;

class UpdateRequest extends StoreRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $id = $this->route('warehouse')->id ?? null;

        $rules['code'] = [
            'required','string','max:64','regex:/^[a-z0-9_-]+$/',
            Rule::unique('warehouses','code')->ignore($id),
        ];
        $rules['parent_id'][] = Rule::notIn([$id]); // нельзя сделать родителем самого себя

        return $rules;
    }
}
