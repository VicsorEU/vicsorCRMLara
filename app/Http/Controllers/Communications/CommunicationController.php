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

    /**
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        return view('communications.index', $this->communicationService->index($request));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAjax(Request $request): JsonResponse
    {
        return response()->json($this->communicationService->renderTable($request));
    }

    /**
     * @param OnlineChat $chat
     *
     * @return View
     */
    public function show(OnlineChat $chat): View
    {
        return view('communications.show', compact('chat'));
    }
}
