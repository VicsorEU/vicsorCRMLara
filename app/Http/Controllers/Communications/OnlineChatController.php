<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\OnlineChats\StoreRequest;
use App\Http\Requests\Settings\OnlineChats\UpdateRequest;
use App\Models\OnlineChats\OnlineChat;
use App\Services\Communications\CommunicationInterface;
use App\Services\Communications\OnlineChat\OnlineChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnlineChatController extends Controller
{
    protected CommunicationInterface $communicationService;
    protected OnlineChatService $onlineChatService;

    public function __construct(CommunicationInterface $communicationService, OnlineChatService $onlineChatService)
    {
        $this->communicationService = $communicationService;
        $this->onlineChatService = $onlineChatService;
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        return response()->json($this->communicationService->store($data));
    }

    /**
     * @param OnlineChat $onlineChat
     *
     * @return View
     */
    public function edit(OnlineChat $onlineChat): View
    {
        $onlineChat->work_days_array = $onlineChat->work_days ? explode(',', $onlineChat->work_days) : [];
        return view('settings.online_chats.edit', compact('onlineChat'));
    }

    /**
     * @param UpdateRequest $request
     * @param OnlineChat $onlineChat
     *
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, OnlineChat $onlineChat): JsonResponse
    {
        $data = $request->validated();

        return response()->json($this->onlineChatService->updateCompanyChat($onlineChat, $data));
    }

    /**
     * @param OnlineChat $onlineChat
     *
     * @return JsonResponse
     */
    public function destroy(OnlineChat $onlineChat): JsonResponse
    {
        $onlineChat->delete();
        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * @param OnlineChat $onlineChat
     *
     * @return JsonResponse
     */
    public function listOfMessages(OnlineChat $onlineChat): JsonResponse
    {
        return response()->json($this->onlineChatService->listOfMessages($onlineChat));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        return response()->json([$this->communicationService->sendMessage($request)]);
    }

    /**
     * @param OnlineChat $onlineChat
     *
     * @return JsonResponse
     */
    public function unreadCountMessages(OnlineChat $onlineChat): JsonResponse
    {
        return response()->json($this->onlineChatService->unreadCountMessages($onlineChat));
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
}
