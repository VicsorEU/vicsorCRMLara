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
    public function join($token)
    {
        $chat = OnlineChat::where('token', $token)->first();
        if (!$chat) return false;

        return ['token' => $token];
    }
}
