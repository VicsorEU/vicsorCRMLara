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

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        return response()->json($this->communicationService->store($data));
    }

    public function edit(OnlineChat $onlineChat)
    {
        $onlineChat->work_days_array = $onlineChat->work_days ? explode(',', $onlineChat->work_days) : [];
        return view('settings.online_chats.edit', compact('onlineChat'));
    }

    public function update(UpdateRequest $request, OnlineChat $onlineChat)
    {
        $data = $request->validated();

        return response()->json($this->onlineChatService->updateCompanyChat($onlineChat, $data));
    }

    public function destroy(OnlineChat $onlineChat)
    {
        $onlineChat->delete();
        return response()->json([
            'success' => true,
        ]);
    }


    public function listOfMessages(OnlineChat $onlineChat)
    {
        return response()->json($this->onlineChatService->listOfMessages($onlineChat));
    }


    public function sendMessage(Request $request)
    {
        return response()->json([$this->communicationService->sendMessage($request)]);
    }

    public function unreadCountMessages(OnlineChat $onlineChat)
    {
        return response()->json($this->onlineChatService->unreadCountMessages($onlineChat));
    }

    public function checkOnNewMessages(Request $request)
    {
        return response()->json($this->onlineChatService->checkOnNewMessages($request));
    }
}
