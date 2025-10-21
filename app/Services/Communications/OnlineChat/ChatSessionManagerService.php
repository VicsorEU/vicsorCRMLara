<?php

namespace App\Services\Communications\OnlineChat;

use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use App\Services\Communications\OnlineChat\Helpers\HasUpdateStatusMessage;
use Illuminate\Support\Facades\Log;


class ChatSessionManagerService
{
    use HasUpdateStatusMessage;

    protected OnlineChatService $onlineChatService;
    public function __construct(OnlineChatService $onlineChatService) {
        $this->onlineChatService = $onlineChatService;
    }

    /**
     * @param OnlineChat $onlineChat
     * @param string|null $message
     * @param int $type
     *
     * @return OnlineChatData|null
     */
    public function handleMessage(OnlineChat $onlineChat, ?string $message, int $type): ?OnlineChatData
    {
        try {
            //        $isWork = CheckOnWork::isWork($onlineChat);

            OnlineChatData::query()
                ->where('online_chat_id', $onlineChat->id)
                ->whereNot('type', $type)
                ->update(['status' => OnlineChatData::STATUS_READ]);

            $onlineChatData = $this->onlineChatService->createMessage($onlineChat, $message, $type);
            $this->updateMessageStatus($onlineChatData, OnlineChatData::STATUS_SENT);

            return $onlineChatData;

        } catch (\Throwable $e) {
            Log::error('Ошибка при обработке сообщения онлайн-чата: ' . $e->getMessage(), [
                'chat_id' => $onlineChat->id,
                'message' => $message,
                'type' => $type,
                'stack' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
