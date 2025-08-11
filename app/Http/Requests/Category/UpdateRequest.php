<?php

namespace App\Http\Requests\Category;

use Illuminate\Validation\Rule;

class UpdateRequest extends StoreRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $id = $this->route('category')->id ?? null;

        // slug уникален, но игнорим текущую запись
        $rules['slug'] = [
            'required','string','max:191','regex:/^[a-z0-9-]+$/',
            Rule::unique('categories','slug')->ignore($id)
        ];

        return array_merge($rules, [
            'remove_image' => ['nullable','boolean'],
            // запрещаем делать родителем саму себя
            'parent_id' => ['nullable','exists:categories,id', Rule::notIn([$id])],
        ]);
    }
}
