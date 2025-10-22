<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use App\Models\MailChats\MailChat;
use App\Models\MailChats\MailChatData;

class MailChatController extends Controller
{
    public function show(MailChat $mailChat)
    {
        return view('communications.mail_chats.show', compact('mailChat'));
    }

    public function listOfMessages(MailChat $mailChat)
    {
        try {
            $onlineChatData = MailChatData::query()
                ->where('mail_chat_id', $mailChat->id)
                ->orderByDesc('created_at')
                ->get();

            MailChatData::query()
                ->where('mail_chat_id', $mailChat->id)
                ->where('type', MailChatData::TYPE_INCOMING)
                ->where('status', MailChatData::STATUS_SENT)
                ->update([
                    'status' => MailChatData::STATUS_READ,
                ]);

            $data = [
                'success' => true,
                'online_chat_session' => $onlineChatData,
            ];
        } catch (\Throwable $e) {
            $data = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return response()->json($data);
    }
}
