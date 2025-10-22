<?php

namespace App\Services\Communications\MailChat;

use App\Models\MailChats\MailChat;
use Illuminate\Support\Facades\Log;

class MailChatService
{
    /**
     * @param array $data
     *
     * @return MailChat|null
     */
    public function createMailChat(array $data): ?MailChat
    {
        try {
            $data['token'] = bin2hex(random_bytes(20));
            $data['work_days'] = implode(',', $data['work_days']);

            return MailChat::create($data);

        } catch (\Throwable $e) {
            Log::error('Error MailChat create: ' . $e->getMessage());
            return null;
        }
    }

    public function updateMailChat(MailChat $mailChat, array $data)
    {
        try {
            $data['work_days'] = implode(',', $data['work_days']);

            $mailChat->update($data);

            return [
                'success' => true,
                'mailChat' => $mailChat->refresh(),
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
