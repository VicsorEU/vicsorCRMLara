<?php

namespace App\Services\Communications\OnlineChat\Helpers;

use App\Models\OnlineChats\OnlineChatData;

trait HasUpdateStatusMessage
{
    /**
     * @param OnlineChatData $onlineChatData
     * @param int $status
     *
     * @return void
     */
    public function updateMessageStatus(OnlineChatData $onlineChatData, int $status): void
    {
        $onlineChatData->update(['status' => $status]);
    }
}
