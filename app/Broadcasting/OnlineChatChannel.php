<?php

namespace App\Broadcasting;

use App\Models\OnlineChats\OnlineChat;
use App\Models\User;

class OnlineChatChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user, $chatId)
    {
        $chat = OnlineChat::find($chatId);
        if (!$chat) return false;

        return ['id' => $user->id];
    }
}
