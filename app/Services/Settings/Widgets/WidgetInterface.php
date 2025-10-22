<?php

namespace App\Services\Settings\Widgets;

use Illuminate\Http\Request;

interface WidgetInterface
{
    public function renderEditTab(Request $request);
    public function updateWidgetChat(array $data);
    public function destroyWidgetChat(Request $request);
}
