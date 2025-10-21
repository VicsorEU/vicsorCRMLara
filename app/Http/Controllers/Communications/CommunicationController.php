<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use App\Models\OnlineChats\OnlineChat;
use App\Services\Communications\CommunicationInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    public function indexAjax(Request $request)
    {
        return response()->json($this->communicationService->renderTable($request));
    }

    public function show(OnlineChat $chat)
    {
        return view('communications.show', compact('chat'));
    }
}
