<?php

namespace App\Services\Communications;

use App\Models\OnlineChats\OnlineChat;
use App\Models\User;
use Illuminate\Http\Request;

interface CommunicationInterface
{
    public function index(Request $request);

    public function store(array $data): array;

    public function renderTable(Request $request): array;

    public function sendMessage(Request $request);
}
