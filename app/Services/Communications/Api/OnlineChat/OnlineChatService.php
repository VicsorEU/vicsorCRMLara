<?php

namespace App\Services\Communications\Api\OnlineChat;

use App\Http\Requests\Api\OnlineChat\StoreRequest;
use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use App\Models\OnlineChats\OnlineChatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnlineChatService implements OnlineChatInterface
{

    public function getSettings(string $token)
    {
        try {
            if (empty($token) || !is_string($token)) {
                return [
                    'success' => false,
                    'message' => 'Invalid token'
                ];
            }

            $widget = OnlineChat::where('token', $token)->first();

            if (!$widget) {
                return [
                    'success' => false,
                    'message' => 'Widget not found'
                ];
            }

            $data = [
                'name' => $widget->name ?? 'Онлайн чат',
                'work_days' => explode(',', $widget->work_days),
                'work_from' => $widget->work_from,
                'work_to' => $widget->work_to,
                'widget_color' => $widget->widget_color ?? '#4F46E5',
                'telegram' => $widget->telegram ?? null,
                'instagram' => $widget->instagram ?? null,
                'facebook' => $widget->facebook ?? null,
                'viber' => $widget->viber ?? null,
                'whatsapp' => $widget->whatsapp ?? null,
                'title' => $widget->title ?? 'Чат с поддержкой',
                'online_text' => $widget->online_text ?? 'Онлайн — готовы помочь',
                'offline_text' => $widget->offline_text ?? 'Оставьте сообщение — мы свяжемся с вами',
                'placeholder' => $widget->placeholder ?? 'Введите сообщение...',
                'greeting_offline' => $widget->greeting_offline ?? 'Здравствуйте!',
                'greeting_online' => $widget->greeting_online ?? 'Здравствуйте!',
                'pusher_key' => env('PUSHER_APP_KEY') ?? '',
                'pusher_cluster' => env('PUSHER_APP_CLUSTER') ?? '',
            ];

            return [
                'success' => true,
                'data' => $data
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching widget settings: ' . $e->getMessage()
            ];
        }
    }


    public function getMessages(string $token, string $authId)
    {
        try {
            if (empty($token) || !is_string($token)) {
                return [
                    'success' => false,
                    'message' => 'Invalid token'
                ];
            }

            if (empty($authId) || !is_string($authId)) {
                return [
                    'success' => false,
                    'message' => 'Invalid or missing auth_id',
                    'messages' => []
                ];
            }

            $onlineChat = OnlineChat::where('token', $token)->first();
            if (!$onlineChat) {
                return [
                    'success' => false,
                    'message' => 'Chat not found'
                ];
            }

            $onlineChatUser = OnlineChatUser::query()
                ->where('auth_id', $authId)
                ->first();
            if (!$onlineChatUser) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                    'messages' => []
                ];
            }

            $onlineChatData = OnlineChatData::query()
                ->where('online_chat_user_id', $onlineChatUser->id)
                ->where('online_chat_id', $onlineChat->id)
                ->orderByDesc('created_at')
                ->get();

            if ($onlineChatData->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'No messages to update',
                    'online_chat_data' => []
                ];
            }

            foreach ($onlineChatData as $message) {
                if (
                    $message->status === OnlineChatData::STATUS_SENT
                    && $message->type === OnlineChatData::TYPE_OUTGOING
                ) {
                    $message->update(['status' => OnlineChatData::STATUS_READ]);
                }
            }

            return [
                'success' => true,
                'online_chat_data' => $onlineChatData,
            ];

        } catch (\Exception $e) {
            Log::error('Error marking messages as read: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error updating messages: ' . $e->getMessage()
            ];
        }
    }

    public function checkOnNewMessages(Request $request)
    {
        try {
            $token = $request->query('token');
            if (empty($token) || !is_string($token)) {
                return [
                    'success' => false,
                    'message' => 'Invalid or missing token',
                    'messages' => []
                ];
            }

            $authId = $request->query('auth_id');
            if (empty($authId) || !is_string($authId)) {
                return [
                    'success' => false,
                    'message' => 'Invalid or missing auth_id',
                    'messages' => []
                ];
            }

            $onlineChatExists = OnlineChat::where('token', $token)->exists();
            if (!$onlineChatExists) {
                return [
                    'success' => false,
                    'message' => 'Chat not found',
                    'messages' => []
                ];
            }

            $onlineChatUser = OnlineChatUser::query()
                ->where('auth_id', $authId)
                ->first();
            if (!$onlineChatUser) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                    'messages' => []
                ];
            }

            $query = OnlineChatData::query()
                ->where('online_chat_user_id', $onlineChatUser->id)
                ->where('type', OnlineChatData::TYPE_OUTGOING)
                ->where('status', OnlineChatData::STATUS_SENT)
                ->whereHas('onlineChat', function ($q) use ($token) {
                    $q->where('token', $token);
                });

            $messages = $query->get();

            $data = [
                'count' => $messages->count(),
                'messages' => $messages->where('notified', false),
            ];

            foreach ($messages as $message) {
                $message->update(['notified' => true]);
            }

            return [
                'success' => true,
                'count' => $data['count'],
                'messages' => $data['messages'],
            ];

        } catch (\Exception $e) {
            Log::error('Error checking new messages: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error checking new messages: ' . $e->getMessage(),
                'messages' => []
            ];
        }
    }


    public function updateMessageStatus(StoreRequest $request)
    {
        try {
            $request->validated();

            $message = OnlineChatData::find($request->id);
            if (!$message) {
                return [
                    'success' => false,
                    'message' => 'Message not found!',
                    'data' => null
                ];
            }

            $message->update([
                'status' => OnlineChatData::STATUS_READ
            ]);

            return [
                'success' => true,
                'message' => 'Статус сообщения обновлён',
                'data' => [
                    'id' => $message->id,
                    'status' => $message->status
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error marking message read: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Ошибка при обновлении статуса сообщения: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}

