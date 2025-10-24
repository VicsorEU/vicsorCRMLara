<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OnlineChat\StoreRequest;
use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatUser;
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


    public function getMessages(string $token, string $authId)
    {
        return response()->json($this->onlineChatService->getMessages($token, $authId));
    }


    public function checkOnNewMessages(Request $request)
    {
        return response()->json($this->onlineChatService->checkOnNewMessages($request));
    }

    public function updateMessageStatus(StoreRequest $request)
    {
        return response()->json($this->onlineChatService->updateMessageStatus($request));
    }

    public function registerUser(Request $request)
    {
        $request->validate([
            'auth_id' => 'required|string|unique:online_chat_users,auth_id',
            'token' => 'required|string|exists:online_chats,token',
        ]);

        $chat = OnlineChat::where('token', $request->token)->first();

        $user = OnlineChatUser::query()
            ->where('auth_id', $request->auth_id)
            ->first();

        if ($user) {
            return response()->json(['success' => true, 'user_id' => $user->id]);
        }

        $user = OnlineChatUser::create([
            'auth_id' => $request->auth_id,
            'online_chat_id' => $chat->id,
        ]);

        return response()->json(['success' => true, 'user_id' => $user->id]);
    }
}
