<?php

namespace App\Services\Communications;

use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use App\Services\Communications\OnlineChat\ChatSessionManagerService;
use App\Services\Communications\OnlineChat\OnlineChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunicationService implements CommunicationInterface
{
    protected OnlineChatService $onlineChatService;
    protected ChatSessionManagerService $chatSessionManagerService;

    public function __construct(OnlineChatService $onlineChatService, ChatSessionManagerService $chatSessionManagerService)
    {
        $this->onlineChatService = $onlineChatService;
        $this->chatSessionManagerService = $chatSessionManagerService;
    }

    public function index(Request $request)
    {
        $section = $request->query('section', 'general');
        $search = trim((string)$request->get('search'));

        $allowedSections = ['general', 'telegram', 'emails'];
        if (!in_array($section, $allowedSections, true)) {
            $section = 'general';
        }

        $chats = null;

        switch ($section) {
            case 'general':
                $chats = OnlineChat::query()
                    ->where('user_id', Auth::id())
                    ->when($search, function ($qq, $s) {
                        $qq->where(fn($w)=>$w
                            ->where('name','ILIKE',"%$s%")
                            ->orWhere('title','ILIKE',"%$s%"));
                    })
                    ->withCount(['onlineChatData as unread_messages_count' => function ($q) {
                        $q->where('status', OnlineChatData::STATUS_SENT);
                    }])
                    ->orderByDesc('created_at')
                    ->paginate(15)
                    ->withQueryString();
                break;

            case 'telegram':

                break;

            case 'emails':

                break;
        }

        return [
            'chats' => $chats,
            'section' => $section,
            'search' => $search,
        ];
    }
    public function store(array $data): array
    {
        $type = $data['type'];

        if (!in_array($type, ['onlineChat', 'telegramChat', 'emailChat'])) {
            return [
                'success' => false,
                'message' => 'Unknown chat type!'
            ];
        }

        if ($type === 'onlineChat') {
            $chat = $this->onlineChatService->createCompanyChat($data);
        }

        return [
            'success' => true,
            'chat_id' => $chat->id,
        ];
    }

    public function renderTable(Request $request): array
    {
        $search = $request->get('search');
        $section = $request->get('section');

        $allowedSections = ['general', 'telegram', 'emails'];
        if (!in_array($section, $allowedSections, true)) {
            $section = 'general';
        }

        switch ($section) {
            case 'general':
                $chats = OnlineChat::query()
                    ->where('user_id', Auth::id())
                    ->when($search, function ($qq, $s) {
                        $qq->where(fn($w)=>$w
                            ->where('name','ILIKE',"%$s%")
                            ->orWhere('title','ILIKE',"%$s%"));
                    })
                    ->withCount(['onlineChatData as unread_messages_count' => function ($q) {
                        $q->where('status', OnlineChatData::STATUS_SENT)
                        ->where('type', OnlineChatData::TYPE_INCOMING);
                    }])
                    ->orderByDesc('created_at')
                    ->paginate(15)
                    ->withQueryString();

                return [
                    'success' => true,
                    'html' => view('communications._table', compact('chats'))->render(),
                ];

            case 'telegram': return [];
            case 'emails' :return [];
            default:
                return [
                    'success' => true,
                    'html' => '<div class="text-red-500">Раздел не найден</div>'
                ];
        }
    }

    public function sendMessage(Request $request)
    {
        $token = $request->input('token');
        $message = $request->input('message');

        $onlineChat = OnlineChat::query()->where('token', $token)->first();
        if (empty($onlineChat)) {
            return [
                'success' => true,
                'message' => 'Unknown chat id!',
            ];
        }

        $type = $request->get('type');

        $this->chatSessionManagerService->handleMessage($onlineChat, $message, $type);

        return [
            'success' => true,
            'message' => $message,
        ];
    }
}
