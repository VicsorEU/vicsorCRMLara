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

    /**
     * @param string $token
     *
     * @return JsonResponse
     */
    public function getSettings(string $token): JsonResponse
    {
        return response()->json($this->onlineChatService->getSettings($token));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
         return response()->json($this->communicationService->sendMessage($request));
    }

    /**
     * @param string $token
     *
     * @return JsonResponse
     */
    public function getMessages(string $token): JsonResponse
    {
        return response()->json($this->onlineChatService->getMessages($token));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function checkOnNewMessages(Request $request): JsonResponse
    {
        return response()->json($this->onlineChatService->checkOnNewMessages($request));
    }

    /**
     * @param StoreRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMessageStatus(StoreRequest $request): JsonResponse
    {
        return response()->json($this->onlineChatService->updateMessageStatus($request));
    }
}
