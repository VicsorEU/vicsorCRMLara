<?php

use App\Broadcasting\OnlineChatChannel;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
*/

Broadcast::channel('online-chat.{chatId}', OnlineChatChannel::class);


