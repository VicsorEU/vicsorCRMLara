<?php

namespace App\Http\Requests\Attribute;

use Illuminate\Validation\Rule;

class UpdateRequest extends StoreRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $id = $this->route('attribute')->id ?? null;

        $rules['slug'] = [
            'required','string','max:191','regex:/^[a-z0-9-]+$/',
            Rule::unique('attributes','slug')->ignore($id),
        ];

        // запрещаем выбирать родителя = сам себе
        $rules['parent_id'][] = Rule::notIn([$id]);

        // разрешим передавать id значений для апдейта
        $rules['values.*.id'] = ['nullable','integer','exists:attribute_values,id'];

        return $rules;
    }
}
