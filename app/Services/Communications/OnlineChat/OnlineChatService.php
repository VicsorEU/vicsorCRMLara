<?php

namespace App\Services\Communications\OnlineChat;

use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnlineChatService
{
    /**
     * @param array $data
     *
     * @return OnlineChat|null
     */
    public function createCompanyChat(array $data): ?OnlineChat
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

    /**
     * @param OnlineChat $onlineChat
     * @param array $data
     *
     * @return array
     */
    public function updateCompanyChat(OnlineChat $onlineChat, array $data): array
    {
        try {
            $data['work_days'] = implode(',', $data['work_days']);

            if (empty($data['token'])) {
                $data['token'] = bin2hex(random_bytes(20));
            }

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

    /**
     * @param OnlineChat $onlineChat
     * @param string $message
     * @param int $type
     *
     * @return mixed
     */
    public function createMessage(OnlineChat $onlineChat, string $message, int $type): array
    {
        return OnlineChatData::create([
            'online_chat_id' => $onlineChat->id,
            'message' => $message,
            'type' => $type,
            'status' => OnlineChatData::STATUS_CREATED,
        ]);
    }

    /**
     * @param OnlineChat $onlineChat
     *
     * @return array
     */
    public function listOfMessages(OnlineChat $onlineChat): array
    {
        try {
            $onlineChatData = OnlineChatData::query()
                ->where('online_chat_id', $onlineChat->id)
                ->orderByDesc('created_at')
                ->get();

            OnlineChatData::query()
                ->where('online_chat_id', $onlineChat->id)
                ->where('type', OnlineChatData::TYPE_INCOMING)
                ->where('status', OnlineChatData::STATUS_SENT)
                ->update([
                    'status' => OnlineChatData::STATUS_READ,
                ]);

            return [
                'success' => true,
                'online_chat_session' => $onlineChatData,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param OnlineChat $onlineChat
     *
     * @return array
     */
    public function unreadCountMessages(OnlineChat $onlineChat): array
    {
        try {
            $messages = OnlineChatData::query()
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

    /**
     * @param Request $request
     *
     * @return array
     */
    public function checkOnNewMessages(Request $request): array
    {
        try {
            $query = OnlineChatData::query()
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
