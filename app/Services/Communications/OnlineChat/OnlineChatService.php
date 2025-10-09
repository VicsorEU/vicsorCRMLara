<?php

namespace App\Services\Communications\OnlineChat;

use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;

class OnlineChatService
{
    public function createCompanyChat(array $data)
    {
        $data['token'] = bin2hex(random_bytes(20));
        $data['work_days'] = implode(',', $data['work_days']);

        return OnlineChat::create($data);
    }

    public function updateCompanyChat(OnlineChat $onlineChat, array $data)
    {
        $data['work_days'] = implode(',', $data['work_days']);

        return $onlineChat::updateOrCreate([
            'token' => $data['token'],
        ],
            $data
        );
    }

    public function createMessage(OnlineChat $onlineChat, string $message, int $type)
    {
        return OnlineChatData::create([
            'online_chat_id' => $onlineChat->id,
            'message' => $message,
            'type' => $type,
            'status' => OnlineChatData::STATUS_CREATED,
        ]);
    }
}
