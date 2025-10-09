<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communications\StoreRequest;
use App\Models\OnlineChats\OnlineChat;
use App\Models\OnlineChats\OnlineChatData;
use App\Models\OnlineChats\OnlineChatSession;
use App\Models\User;
use App\Services\Communications\CommunicationInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunicationController extends Controller
{
    protected CommunicationInterface $communicationService;

    public function __construct(CommunicationInterface $communicationService)
    {
        $this->communicationService = $communicationService;
    }

    public function index(Request $request)
    {
        return view('communications.index', $this->communicationService->index($request));
    }

    public function indexAjax(Request $request): JsonResponse
    {
        return response()->json($this->communicationService->renderTable($request));
    }

    public function show(OnlineChat $chat)
    {
        return view('communications.show', compact('chat'));
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        return response()->json($this->communicationService->store($data));
    }

    public function unreadCountMessages()
    {
        $unreadCountMessages = OnlineChatData::query()
            ->where('type', OnlineChatData::TYPE_INCOMING)
            ->where('status', OnlineChatData::STATUS_SENT)
            ->with(['onlineChatSession' => function ($query) {
                $query->with(['onlineChat' => function ($q) {
                    $q->where('user_id', Auth::id());
                }]);
            }])
            ->count();

        return response()->json([
            'success' => true,
            'count' => $unreadCountMessages,
        ]);
    }
}
