<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\OnlineChats\StoreRequest;
use App\Http\Requests\Settings\OnlineChats\UpdateRequest;
use App\Services\Communications\CommunicationInterface;
use App\Services\Settings\Widgets\WidgetInterface;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    protected CommunicationInterface $communicationService;
    protected WidgetInterface $widgetService;

    public function __construct(CommunicationInterface $communicationService, WidgetInterface $widgetService)
    {
        $this->communicationService = $communicationService;
        $this->widgetService = $widgetService;
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        return response()->json($this->communicationService->store($data));
    }

    public function edit(Request $request)
    {
        $res = $this->widgetService->renderEditTab($request);
        return $res['success'] ? $res['html'] : redirect()->back();
    }

    public function update(UpdateRequest $request)
    {
        $data = $request->validated();
        return response()->json($this->widgetService->updateWidgetChat($data));
    }

    public function destroy(Request $request)
    {
        return response()->json($this->widgetService->destroyWidgetChat($request));
    }
}
