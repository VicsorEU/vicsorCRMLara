<?php

namespace App\Events;

use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageOnlineChat implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OnlineChatData $onlineChatData;
    public OnlineChat $onlineChat;

    public function __construct(OnlineChatData $onlineChatData, OnlineChat $onlineChat)
    {
        $this->onlineChatData = $onlineChatData;
        $this->onlineChat = $onlineChat;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('online-chat.' . $this->onlineChat->id);
    }

    public function broadcastAs(): string
    {
        return 'new-message-online-chat';
    }

    public function broadcastWith(): array
    {
        return [
            'name' => $this->onlineChat->name,
            'preview' => mb_strimwidth($this->onlineChatData->message ?? '', 0, 50, '...'),
            'token' => $this->onlineChat->token,
        ];
    }
}
