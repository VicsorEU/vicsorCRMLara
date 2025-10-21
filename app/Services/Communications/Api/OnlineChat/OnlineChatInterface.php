<?php

namespace App\Services\Communications\Api\OnlineChat;

use App\Http\Requests\Api\OnlineChat\StoreRequest;
use Illuminate\Http\Request;

interface OnlineChatInterface
{
    /**
     * @param string $token
     *
     * @return array
     */
    public function getSettings(string $token): array;

    /**
     * @param string $token
     *
     * @return array
     */
    public function getMessages(string $token): array;

    /**
     * @param Request $request
     *
     * @return array
     */
    public function checkOnNewMessages(Request $request): array;

    /**
     * @param StoreRequest $request
     *
     * @return array
     */
    public function updateMessageStatus(StoreRequest $request): array;
}
