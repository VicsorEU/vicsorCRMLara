<?php

namespace App\Services\Warehouses;

use App\Models\Warehouse;
use Illuminate\Http\Request;

interface WarehouseInterface
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function store(array $data): array;

    /**
     * @param Warehouse $warehouse
     * @param Request $request
     *
     * @return array
     */
    public function edit(Warehouse $warehouse, Request $request): array;

    /**
     * @param Warehouse $warehouse
     * @param array $data
     *
     * @return array
     */
    public function update(Warehouse $warehouse, array $data): array;

    /**
     * @param Warehouse $warehouse
     *
     * @return array
     */
    public function destroy(Warehouse $warehouse): array;
}
