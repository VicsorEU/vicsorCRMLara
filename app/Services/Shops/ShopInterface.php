<?php

namespace App\Services\Shops;

use Illuminate\Http\Request;

interface ShopInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request): array;

    /**
     * @param Request $request
     *
     * @return array
     */
    public function create(Request $request): array;
}
