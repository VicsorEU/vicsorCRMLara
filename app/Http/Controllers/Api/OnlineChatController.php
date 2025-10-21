<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OnlineChat\StoreRequest;
use App\Services\Communications\Api\OnlineChat\OnlineChatInterface;
use App\Services\Communications\CommunicationInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnlineChatController extends Controller
{
    protected CommunicationInterface $communicationService;
    protected OnlineChatInterface $onlineChatService;

    public function __construct(CommunicationInterface $communicationService, OnlineChatInterface $onlineChatService)
    {
        $this->communicationService = $communicationService;
        $this->onlineChatService = $onlineChatService;
    }


    public function getSettings(string $token)
    {
        return response()->json($this->onlineChatService->getSettings($token));
    }


    public function sendMessage(Request $request)
    {
         return response()->json($this->communicationService->sendMessage($request));
    }


    public function getMessages(string $token)
    {
        return response()->json($this->onlineChatService->getMessages($token));
    }


    public function checkOnNewMessages(Request $request)
    {
        return response()->json($this->onlineChatService->checkOnNewMessages($request));
    }

    public function updateMessageStatus(StoreRequest $request)
    {
        return response()->json($this->onlineChatService->updateMessageStatus($request));
    }
}
