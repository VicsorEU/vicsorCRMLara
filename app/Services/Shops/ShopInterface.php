<?php

namespace App\Services\Shops;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface ShopInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request): array;

    /**
     * @param string $section
     * @param $items
     *
     * @return string
     */
    public function renderTable(string $section, $items): string;

    /**
     * @param Request $request
     *
     * @return array
     */
    public function create(Request $request): array;
}
