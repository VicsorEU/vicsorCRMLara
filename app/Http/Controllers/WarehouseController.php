<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\User;
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
     * @return RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $res = $this->warehouseService->store($data);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return redirect()
            ->route('shops.warehouse.edit', [
                'section' => 'warehouses',
                'warehouse' => $res['warehouse'],
            ])
            ->with('status','Склад создан');
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
     * @return RedirectResponse
     */
    public function update(UpdateRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $data = $request->validated();

        $res = $this->warehouseService->update($warehouse, $data);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return redirect()
            ->route('shops.warehouse.edit', [
                'section' => 'warehouses',
                'warehouse' => $warehouse->fresh(),
            ])
            ->with('status','Сохранено');
    }

    /**
     * @param Warehouse $warehouse
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $res = $this->warehouseService->destroy($warehouse);
        if (!$res['success']) {
            return response()->json([
                'success' => false,
                'message' => $res['message'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Склад удален успешно',
        ]);
    }
}
