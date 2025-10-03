<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Http\Requests\Warehouse\StoreRequest;
use App\Http\Requests\Warehouse\UpdateRequest;
use App\Services\Warehouses\WarehouseInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    protected WarehouseInterface $warehouseService;

    public function __construct(WarehouseInterface $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }
    public function index()
    {
       //
    }

    public function create()
    {
        //
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $res = $this->warehouseService->store($data);

        return response()->json([
            'success'   => $res['success'],
            'message'   => $res['message'],
            'warehouse' => $res['warehouse'] ?? null,
        ]);
    }

    /**
     * @param Warehouse $warehouse
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|RedirectResponse|object
     */
    public function edit(Warehouse $warehouse, Request $request): mixed
    {
        $res = $this->warehouseService->edit($warehouse, $request);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return view('shops.edit', [
            'section'   => $res['section'],
            'warehouse' => $res['warehouse'],
            'parents'   => $res['parents'],
            'managers'  => $res['managers'],
        ]);
    }

    /**
     * @param UpdateRequest $request
     * @param Warehouse $warehouse
     *
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, Warehouse $warehouse): JsonResponse
    {
        $data = $request->validated();

        $res = $this->warehouseService->update($warehouse, $data);

        return response()->json([
            'success'   => $res['success'],
            'message'   => $res['message'],
            'warehouse' => $res['warehouse'] ?? null,
        ]);
    }

    /**
     * @param Warehouse $warehouse
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $res = $this->warehouseService->destroy($warehouse);

        return response()->json([
            'success'   => $res['success'],
            'message'   => $res['message'],
        ]);
    }
}
