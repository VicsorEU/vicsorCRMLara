<?php

namespace App\Services\Settings\Widgets;

use App\Models\MailChats\MailChat;
use App\Models\OnlineChats\OnlineChat;
use App\Services\Communications\MailChat\MailChatService;
use App\Services\Communications\OnlineChat\OnlineChatService;
use Illuminate\Http\Request;

class WidgetService implements WidgetInterface
{
    protected OnlineChatService $onlineChatService;
    protected MailChatService $mailChatService;

    public function __construct(OnlineChatService $onlineChatService, MailChatService $mailChatService)
    {
        $this->mailChatService = $mailChatService;
        $this->onlineChatService = $onlineChatService;
    }
    public function renderEditTab(Request $request)
    {
        $section = $request->query('section', 'general');
        $chatId = $request->query('chat_id');

        $allowedSections = ['general', 'telegram', 'emails'];
        if (!in_array($section, $allowedSections, true)) {
            $section = 'general';
        }

        switch ($section) {
            case 'general':
                $onlineChat = OnlineChat::find($chatId);
                if (!$onlineChat) {
                    return [
                        'success' => false,
                        'message' => 'Chat not found',
                    ];
                }

                $onlineChat->work_days_array = $onlineChat->work_days ? explode(',', $onlineChat->work_days) : [];

                return [
                    'success' => true,
                    'html' => view('settings.online_chats.edit', compact('onlineChat')),
                ];

            case 'emails':
                $mailChat = MailChat::find($chatId);
                if (!$mailChat) {
                    return [
                        'success' => false,
                        'message' => 'Chat not found',
                    ];
                }

                $mailChat->work_days_array = $mailChat->work_days ? explode(',', $mailChat->work_days) : [];

                return [
                    'success' => true,
                    'html' => view('settings.online_chats.edit_mail', compact('mailChat')),
                ];
        }

        return [
            'success' => false,
            'message' => 'Chat not found',
        ];
    }

    public function updateWidgetChat(array $data)
    {
        $section = $data['section'];
        $chatId = $data['chat_id'];

        $allowedSections = ['general', 'telegram', 'emails'];
        if (!in_array($section, $allowedSections, true)) {
            $section = 'general';
        }

        switch ($section) {
            case 'general':
                $onlineChat = OnlineChat::find($chatId);

                if (!$onlineChat) {
                    $res = [
                        'success' => false,
                        'message' => 'Chat not found',
                    ];

                    break;
                }

                $res = $this->onlineChatService->updateCompanyChat($onlineChat, $data);
                break;

            case 'telegram':
                break;

            case 'emails':
                $mailChat = MailChat::find($chatId);
                if (!$mailChat) {
                    $res = [
                        'success' => false,
                        'message' => 'Chat not found',
                    ];

                    break;
                }

                $res = $this->mailChatService->updateMailChat($mailChat, $data);
                break;
        }

        return $res;
    }

    public function destroyWidgetChat(Request $request)
    {
        $section = $request->query('section', 'general');
        $chatId = $request->query('chat_id');

        $allowedSections = ['general', 'telegram', 'emails'];
        if (!in_array($section, $allowedSections, true)) {
            $section = 'general';
        }

        switch ($section) {
            case 'general':
                $onlineChat = OnlineChat::find($chatId);

                if (!$onlineChat) {
                    $res = [
                        'success' => false,
                        'message' => 'Chat not found',
                    ];

                    break;
                }

                $onlineChat->delete();

                $res = [
                    'success' => true,
                ];
                break;

            case 'telegram':
                break;

            case 'emails':
                $mailChat = MailChat::find($chatId);
                if (!$mailChat) {
                    $res = [
                        'success' => false,
                        'message' => 'Chat not found',
                    ];

                    break;
                }

                $mailChat->delete();

                $res = [
                    'success' => true,
                ];
                break;
        }

        return $res;
    }
}
