<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use App\Services\Communications\CommunicationInterface;
use Illuminate\Http\Request;

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
}
