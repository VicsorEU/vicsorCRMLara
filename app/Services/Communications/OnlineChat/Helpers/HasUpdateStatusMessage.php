<?php

namespace App\Services\Communications\OnlineChat\Helpers;

use App\Models\OnlineChats\OnlineChatData;

trait HasUpdateStatusMessage
{

    public function updateMessageStatus(OnlineChatData $onlineChatData, int $status)
    {
        $onlineChatData->update(['status' => $status]);
    }
}
