<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => ['required','string','max:191'],
            'slug'        => ['required','string','max:191','regex:/^[a-z0-9-]+$/','unique:categories,slug'],
            'description' => ['nullable','string'],
            'parent_id'   => ['nullable','exists:categories,id'],
            'image'       => ['nullable','image','mimes:jpeg,png,webp','max:3072'], // до ~3 МБ
        ];
    }

    public function attributes(): array
    {
        return ['name'=>'Название','slug'=>'Слаг','parent_id'=>'Родитель','image'=>'Изображение'];
    }
}
