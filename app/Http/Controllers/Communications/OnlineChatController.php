<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\OnlineChats\StoreRequest;
use App\Http\Requests\Settings\OnlineChats\UpdateRequest;
use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use App\Services\Communications\CommunicationInterface;
use App\Services\Communications\OnlineChat\OnlineChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $onlineChat = $this->onlineChatService->updateCompanyChat($onlineChat, $data);

        return response()->json([
            'success' => true,
            'onlineChat' => $onlineChat->refresh(),
        ]);
    }

    public function destroy(OnlineChat $onlineChat)
    {
        $onlineChat->delete();
        return response()->json([
            'success' => true,
        ]);
    }

    public function listOfMessages(OnlineChat $onlineChat): JsonResponse
    {
        $onlineChatData = OnlineChatData::query()
            ->where('online_chat_id', $onlineChat->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success'   => true,
            'online_chat_session' => $onlineChatData,
        ]);
    }

    public function sendMessage(Request $request)
    {
        return response()->json([$this->communicationService->sendMessage($request)]);
    }

    public function unreadCountMessages(OnlineChat $onlineChat)
    {
        $unreadCountMessages = OnlineChatData::query()
            ->where('online_chat_id', $onlineChat->id)
            ->where('type',OnlineChatData::TYPE_INCOMING)
            ->where('status', OnlineChatData::STATUS_SENT)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $unreadCountMessages,
        ]);
    }
}
