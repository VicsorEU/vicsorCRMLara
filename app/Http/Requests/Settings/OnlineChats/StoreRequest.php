<?php

namespace App\Http\Requests\Settings\OnlineChats;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $rules = [
            'type' => ['required', 'in:onlineChat,telegramChat,emailChat'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];

        // Правила для онлайн-чата
        if ($type === 'onlineChat') {
            $rules = array_merge($rules, [
                'name' => ['required', 'string', 'max:255', 'unique:online_chats,name'],
                'work_days' => ['required', 'array'],
                'work_from' => ['required', 'date_format:H:i'],
                'work_to' => ['required', 'date_format:H:i'],
                'widget_color' => ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
                'telegram' => ['nullable', 'string', 'max:255'],
                'instagram' => ['nullable', 'string', 'max:255'],
                'facebook' => ['nullable', 'string', 'max:255'],
                'viber' => ['nullable', 'string', 'max:255'],
                'whatsapp' => ['nullable', 'string', 'max:255'],
                'title' => ['required', 'string', 'max:255'],
                'online_text' => ['required', 'string', 'max:255'],
                'offline_text' => ['required', 'string', 'max:255'],
                'placeholder' => ['required', 'string', 'max:255'],
                'greeting_offline' => ['required', 'string'],
                'greeting_online' => ['required', 'string'],
            ]);
        }

        // Правила для e-mail чата
        if ($type === 'emailChat') {
            $rules = array_merge($rules, [
                'name' => ['required', 'string', 'max:255', 'unique:mail_chats,name'],
                'email' => ['required', 'email', 'unique:mail_chats,email'],
                'mail_type' => ['nullable', 'string'],
                'work_days' => ['nullable', 'array'],
                'work_from' => ['nullable', 'date_format:H:i'],
                'work_to' => ['nullable', 'date_format:H:i'],
                'widget_color' => ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            ]);
        }

        return $rules;
    }
}
