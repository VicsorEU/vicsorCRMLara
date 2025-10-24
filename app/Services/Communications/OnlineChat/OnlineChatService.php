<?php

namespace App\Services\Communications\OnlineChat;

use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use App\Models\OnlineChats\OnlineChatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnlineChatService
{
    public function createCompanyChat(array $data)
    {
        try {
            $data['token'] = bin2hex(random_bytes(20));
            $data['work_days'] = implode(',', $data['work_days']);

            return OnlineChat::create($data);

        } catch (\Throwable $e) {
            Log::error('Error OnlineChat create: ' . $e->getMessage());
            return null;
        }
    }

    public function updateCompanyChat(OnlineChat $onlineChat, array $data)
    {
        try {
            $data['work_days'] = implode(',', $data['work_days']);

            $onlineChat->update($data);

            return [
                'success' => true,
                'onlineChat' => $onlineChat->refresh(),
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function createMessage(OnlineChat $onlineChat, string $message, int $type, ?int $onlineChatUserId, ?string $sourceUrl)
    {
        return OnlineChatData::create([
            'online_chat_id' => $onlineChat->id,
            'online_chat_user_id' => $onlineChatUserId,
            'message' => $message,
            'type' => $type,
            'source_url' => $sourceUrl,
            'status' => OnlineChatData::STATUS_CREATED,
        ]);
    }

    public function listOfMessages(OnlineChat $onlineChat, Request $request)
    {
        try {
            $onlineChatUserId = $request->query('online_chat_user_id');
            $messages = null;

            $allMessages = OnlineChatData::query()
                ->where('online_chat_id', $onlineChat->id)
                ->where('type', OnlineChatData::TYPE_INCOMING)
                ->where('status', OnlineChatData::STATUS_SENT)
                ->get();

            $grouped = $allMessages
                ->groupBy('online_chat_user_id')
                ->map(function ($group, $userId) {
                    return [
                        'online_chat_user_id' => $userId,
                        'count' => $group->count(),
                    ];
                })
                ->values();

            if ($onlineChatUserId) {
                $messages =  OnlineChatData::query()
                    ->where('online_chat_user_id', $onlineChatUserId)
                    ->where('online_chat_id', $onlineChat->id)
                    ->get();

                $messages
                    ->where('type', OnlineChatData::TYPE_INCOMING)
                    ->where('status', OnlineChatData::STATUS_SENT)
                    ->each(function ($item) {
                    $item->update([
                        'status' => OnlineChatData::STATUS_READ,
                    ]);
                });
            }

            return [
                'success' => true,
                'messages' => $messages,
                'grouped' => $grouped,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function unreadCountMessages(OnlineChat $onlineChat, ?int $onlineChatUserId)
    {
        try {
            $messages = OnlineChatData::query()
                ->when($onlineChatUserId, function ($query) use ($onlineChatUserId) {
                    $query->where('online_chat_user_id', $onlineChatUserId);
                })
                ->where('online_chat_id', $onlineChat->id)
                ->where('type', OnlineChatData::TYPE_INCOMING)
                ->where('status', OnlineChatData::STATUS_SENT)
                ->get();


            $messages->each(function ($item) {
                $item->update([
                    'status' => OnlineChatData::STATUS_READ,
                ]);
            });

            return [
                'success' => true,
                'count' => $messages->count(),
                'messages' => $messages,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'count' => 0,
            ];
        }
    }

    public function checkOnNewMessages(Request $request)
    {
        try {
            $onlineChatUserId = $request->query('online_chat_user_id');

            $query = OnlineChatData::query()
                ->when($onlineChatUserId, function ($query) use ($onlineChatUserId) {
                    return $query->where('online_chat_user_id', $onlineChatUserId);
                })
                ->where('type', OnlineChatData::TYPE_INCOMING)
                ->where('status', OnlineChatData::STATUS_SENT)
                ->whereHas('onlineChat', function ($q) use ($request) {
                    if ($request->has('user_id')) {
                        $q->where('user_id', $request->query('user_id'));
                    }
                    if ($request->has('token')) {
                        $q->where('token', $request->query('token'));
                    }
                });

            $messages = $query->get();

            $grouped = $messages->groupBy('online_chat_id')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'online_chat_id' => $group->first()->online_chat_id,
                    'online_chat_user_id' => $group->first()->online_chat_user_id,
                ];
            });

            $data = [
                'success' => true,
                'count' => $messages->count(),
                'grouped' => $grouped,
                'messages' => $messages->where('notified', false),
            ];

            foreach ($messages as $message) {
                $message->update(['notified' => true]);
            }

            return $data;
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'count' => 0,
                'grouped' => [],
            ];
        }
    }
}
