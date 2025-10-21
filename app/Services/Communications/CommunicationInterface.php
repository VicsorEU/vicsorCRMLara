<?php

namespace App\Services\Communications;

use App\Models\OnlineChats\OnlineChat;
use App\Models\User;
use Illuminate\Http\Request;

interface CommunicationInterface
{
    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request);

    /**
     * @param array $data
     *
     * @return array
     */
    public function store(array $data): array;

    /**
     * @param Request $request
     *
     * @return array
     */
    public function renderTable(Request $request): array;

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function sendMessage(Request $request);
}
