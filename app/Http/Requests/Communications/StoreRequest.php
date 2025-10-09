<?php

namespace App\Http\Requests\Communications;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:onlineChat,telegramChat,emailChat'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'work_days' => ['required', 'array'],
            'work_from' => ['required'],
            'work_to' => ['required'],
            'widget_color'   => ['required','string'],
            'widget_color.*' => ['required','regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'telegram' => ['nullable', 'string'],
            'instagram' => ['nullable', 'string'],
            'facebook' => ['nullable', 'string'],
            'viber' => ['nullable', 'string'],
            'whatsapp' => ['nullable', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'online_text' => ['required', 'string', 'max:255'],
            'offline_text' => ['required', 'string', 'max:255'],
            'placeholder' => ['required', 'string', 'max:255'],
            'greeting_offline' => ['required', 'string'],
            'greeting_online' => ['required', 'string'],
        ];
    }
}
