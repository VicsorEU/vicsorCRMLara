<?php

namespace App\Http\Requests\Settings\OnlineChats;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Общие правила для всех типов чатов
        $rules = [
            'section' => ['required', 'in:general,telegram,emails'],
            'type' => ['required', 'in:onlineChat,telegramChat,emailChat'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'work_days' => ['required'],
            'work_from' => ['required', 'date_format:H:i:s'],
            'work_to' => ['required', 'date_format:H:i:s', 'after:work_from'],
            'widget_color' => ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        ];

        // Правила для онлайн-чата
        if ($this->type === 'onlineChat') {
            $rules = array_merge($rules, [
                'chat_id' => ['required', 'integer', 'exists:online_chats,id'],
                'token' => ['nullable', 'string', 'max:255', 'exists:online_chats,token'],
                'title' => ['required', 'string', 'max:255'],
                'online_text' => ['required', 'string', 'max:255'],
                'offline_text' => ['required', 'string', 'max:255'],
                'placeholder' => ['required', 'string', 'max:255'],
                'greeting_offline' => ['required', 'string'],
                'greeting_online' => ['required', 'string'],
            ]);
        }

        // Правила для Email
        if ($this->type === 'emailChat') {
            $rules = array_merge($rules, [
                'chat_id' => ['required', 'integer', 'exists:mail_chats,id'],
                'email' => ['required', 'email', 'max:255'],
                'mail_type' => ['required', 'string', 'max:50'],
                'is_verified' => ['required', 'boolean'],
            ]);
        }

        return $rules;
    }
}
