<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use App\Models\OnlineChats\OnlineChat;
use App\Services\Communications\CommunicationInterface;
use App\Services\Communications\OnlineChat\OnlineChatService;
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

    public function show(OnlineChat $onlineChat)
    {
        return view('communications.online_chats.show', compact('onlineChat'));
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
