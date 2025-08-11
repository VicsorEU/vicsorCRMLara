<?php

namespace App\Http\Requests\Attribute;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => ['required','string','max:191'],
            'slug'        => ['required','string','max:191','regex:/^[a-z0-9-]+$/','unique:attributes,slug'],
            'description' => ['nullable','string'],
            'parent_id'   => ['nullable','exists:attributes,id'],

            // values[n][name|slug|sort_order]
            'values'              => ['array'],
            'values.*.name'       => ['nullable','string','max:191'],
            'values.*.slug'       => ['nullable','string','max:191','regex:/^[a-z0-9-]+$/'],
            'values.*.sort_order' => ['nullable','integer'],
        ];
    }

    public function attributes(): array
    {
        return ['name'=>'Название','slug'=>'Слаг','values.*.name'=>'Значение','values.*.slug'=>'Слаг значения'];
    }
}
