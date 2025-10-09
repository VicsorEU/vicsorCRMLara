<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use App\Services\Communications\CommunicationInterface;
use Illuminate\Http\Request;

class OnlineChatController extends Controller
{
    protected CommunicationInterface $communicationService;

    public function __construct(CommunicationInterface $communicationService)
    {
        $this->communicationService = $communicationService;
    }

    public function getSettings(string $token)
    {
        $widget = OnlineChat::where('token', $token)->first();

        if (!$widget) {
            return response()->json(['success' => false, 'message' => 'Widget not found']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $widget->name,
                'work_days' => explode(',', $widget->work_days),
                'work_from' => $widget->work_from,
                'work_to' => $widget->work_to,
                'widget_color' => $widget->widget_color,
                'telegram' => $widget->telegram,
                'instagram' => $widget->instagram,
                'facebook' => $widget->facebook,
                'viber' => $widget->viber,
                'whatsapp' => $widget->whatsapp,
                'title' => $widget->title,
                'online_text' => $widget->online_text,
                'offline_text' => $widget->offline_text,
                'placeholder' => $widget->placeholder,
                'greeting_offline' => $widget->greeting_offline,
                'greeting_online' => $widget->greeting_online,
            ]
        ]);
    }


    public function sendMessage(Request $request)
    {
         return response()->json($this->communicationService->sendMessage($request));
    }

    public function getMessages(string $token)
    {
        $onlineChat = OnlineChat::where('token', $token)->first();

        $onlineChatData = OnlineChatData::query()
            ->where('online_chat_id', $onlineChat->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success'   => true,
            'online_chat_data' => $onlineChatData,
        ]);
    }
}
