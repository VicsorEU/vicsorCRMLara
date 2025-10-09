<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use App\Services\Communications\CommunicationInterface;
use App\Services\Communications\OnlineChat\OnlineChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnlineChatController extends Controller
{
    protected CommunicationInterface $communicationService;
    protected OnlineChatService $onlineChatService;

    public function __construct(CommunicationInterface $communicationService, OnlineChatService $onlineChatService)
    {
        $this->communicationService = $communicationService;
        $this->onlineChatService = $onlineChatService;
    }

    public function edit(OnlineChat $onlineChat)
    {
        $onlineChat->work_days_array = $onlineChat->work_days ? explode(',', $onlineChat->work_days) : [];
        return view('communications.edit', compact('onlineChat'));
    }

    public function update(Request $request, OnlineChat $onlineChat)
    {
        $data = $request->all();

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
        $this->communicationService->sendMessage($request);
        return view('communications.index');
    }

    public function unreadCountMessages(OnlineChat $onlineChat)
    {
        $unreadCountMessages = OnlineChatData::query()
            ->where('online_chat_id', $onlineChat->id)
            ->where('type', OnlineChatData::TYPE_INCOMING)
            ->where('status', OnlineChatData::STATUS_SENT)
            ->with(['onlineChat' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->count();

        return response()->json([
            'success' => true,
            'count' => $unreadCountMessages,
        ]);
    }
}
