<?php

namespace App\Services\Communications;

use App\Models\MailChats\MailChat;
use App\Models\MailChats\MailChatData;
use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use App\Models\OnlineChats\OnlineChatUser;
use App\Services\Communications\MailChat\MailChatService;
use App\Services\Communications\OnlineChat\ChatSessionManagerService;
use App\Services\Communications\OnlineChat\OnlineChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunicationService implements CommunicationInterface
{
    protected OnlineChatService $onlineChatService;
    protected ChatSessionManagerService $chatSessionManagerService;
    protected MailChatService $mailChatService;

    public function __construct(
        OnlineChatService $onlineChatService,
        MailChatService $mailChatService,
        ChatSessionManagerService $chatSessionManagerService
    )
    {
        $this->onlineChatService = $onlineChatService;
        $this->mailChatService = $mailChatService;
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
                $chats = MailChat::query()
                    ->where('user_id', Auth::id())
                    ->when($search, function ($qq, $s) {
                        $qq->where(fn($w)=>$w
                            ->where('name','ILIKE',"%$s%")
                            ->where('email','ILIKE',"%$s%")
                            ->orWhere('title','ILIKE',"%$s%"));
                    })
                    ->withCount(['mailChatData as unread_messages_count' => function ($q) {
                        $q->where('status', MailChatData::STATUS_SENT);
                    }])
                    ->orderByDesc('created_at')
                    ->paginate(15)
                    ->withQueryString();

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

        if ($type === 'emailChat') {
            $chat = $this->mailChatService->createMailChat($data);
        }

        if (empty($chat)) {
            return [
                'success' => false,
                'message' => 'Could not create chat!'
            ];
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
                    'html' => view('communications.online_chats._table', compact('chats'))->render(),
                ];

            case 'telegram': return [];
            case 'emails' :
                $chats = MailChat::query()
                    ->where('user_id', Auth::id())
                    ->when($search, function ($qq, $s) {
                        $qq->where(fn($w)=>$w
                            ->where('name','ILIKE',"%$s%")
                            ->where('email','ILIKE',"%$s%")
                            ->orWhere('title','ILIKE',"%$s%"));
                    })
                    ->orderByDesc('created_at')
                    ->paginate(15)
                    ->withQueryString();

                return [
                    'success' => true,
                    'html' => view('communications.mail_chats._table', compact('chats'))->render(),
                ];

            default:
                return [
                    'success' => true,
                    'html' => '<div class="text-red-500">Раздел не найден</div>'
                ];
        }
    }

    public function sendMessage(Request $request)
    {
        $token = $request->get('token');
        $message = $request->get('message');
        $authId = $request->get('auth_id');
        $type = $request->get('type');
        $sourceUrl = $request->get('source_url') ?? null;

        if (empty($authId)) {
            return [
                'success' => false,
                'message' => 'Invalid or missing auth_id',
                'messages' => []
            ];
        }

        $onlineChat = OnlineChat::query()->where('token', $token)->first();
        if (empty($onlineChat)) {
            return [
                'success' => true,
                'message' => 'Unknown chat id!',
            ];
        }

        $onlineChatUserQuery = OnlineChatUser::query();

        if ($type === OnlineChatData::TYPE_INCOMING) {
            $onlineChatUserQuery->where('auth_id', $authId);
        } else {
            $onlineChatUserQuery->where('id', $authId);
        }

        $onlineChatUser = $onlineChatUserQuery->first();;
        if (!$onlineChatUser) {
            return [
                'success' => false,
                'message' => 'User not found',
                'messages' => []
            ];
        }

        $this->chatSessionManagerService->handleMessage($onlineChat, $message, $type, $onlineChatUser->id, $sourceUrl);

        return [
            'success' => true,
            'message' => $message,
        ];
    }
}
