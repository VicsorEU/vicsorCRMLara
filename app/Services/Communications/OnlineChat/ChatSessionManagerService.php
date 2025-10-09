<?php

namespace App\Services\Communications\OnlineChat;

use App\Events\NewMessageOnlineChat;
use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use App\Services\Communications\OnlineChat\Helpers\HasUpdateStatusMessage;


class ChatSessionManagerService
{
    use HasUpdateStatusMessage;

    protected OnlineChatService $onlineChatService;
    public function __construct(OnlineChatService $onlineChatService) {
        $this->onlineChatService = $onlineChatService;
    }

    public function handleMessage(OnlineChat $onlineChat, ?string $message, int $type)
    {
//        $isWork = CheckOnWork::isWork($onlineChat);

           // update the status to “read” for all previous messages
        OnlineChatData::query()
            ->where('online_chat_id', $onlineChat->id)
            ->whereNot('type', $type)
            ->update(['status' => OnlineChatData::STATUS_READ]);

        // creating a message
        $onlineChatData = $this->onlineChatService->createMessage($onlineChat, $message, $type);
        $this->updateMessageStatus($onlineChatData, OnlineChatData::STATUS_SENT);

        event(new NewMessageOnlineChat($onlineChatData, $onlineChat));

        return $onlineChatData;
    }
}
