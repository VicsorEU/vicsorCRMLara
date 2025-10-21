<?php

namespace App\Services\Communications\Api\OnlineChat;

use App\Http\Requests\Api\OnlineChat\StoreRequest;
use Illuminate\Http\Request;

interface OnlineChatInterface
{

    public function getSettings(string $token);


    public function getMessages(string $token);


    public function checkOnNewMessages(Request $request);

    public function updateMessageStatus(StoreRequest $request);
}
