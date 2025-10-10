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
        return [
            // Тип чата
            'type' => ['required', 'in:onlineChat,telegramChat,emailChat'],

            // Пользователь
            'user_id' => ['required', 'integer', 'exists:users,id'],

            // Основные параметры
            'name' => ['required', 'string', 'max:255', 'unique:online_chats,name'],

            // Рабочее время
            'work_days' => ['required'],
            'work_from' => ['required', 'date_format:H:i'],
            'work_to' => ['required', 'date_format:H:i'],

            // Внешний вид
            'widget_color' => ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],

            // Соцсети
            'telegram' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'viber' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255'],

            // Тексты виджета
            'title' => ['required', 'string', 'max:255'],
            'online_text' => ['required', 'string', 'max:255'],
            'offline_text' => ['required', 'string', 'max:255'],
            'placeholder' => ['required', 'string', 'max:255'],
            'greeting_offline' => ['required', 'string'],
            'greeting_online' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Введите название виджета',
            'work_days.required' => 'Укажите рабочие дни',
            'work_from.required' => 'Укажите время начала работы',
            'work_to.required' => 'Укажите время окончания работы',
            'title.required' => 'Введите заголовок виджета',
            'online_text.required' => 'Введите текст "онлайн"',
            'offline_text.required' => 'Введите текст "оффлайн"',
            'placeholder.required' => 'Введите текст для поля ввода',
            'greeting_offline.required' => 'Введите приветствие для нерабочего времени',
            'greeting_online.required' => 'Введите приветствие для рабочего времени',
            'widget_color.regex' => 'Цвет должен быть в формате HEX (например, #ff6600)',
        ];
    }
}
