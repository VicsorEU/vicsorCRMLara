<?php

namespace App\Http\Requests\Api\OnlineChat;

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
            'id' => 'required|integer|exists:online_chat_data,id',
        ];
    }
}
