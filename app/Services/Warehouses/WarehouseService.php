<?php

namespace App\Services\Warehouses;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WarehouseService implements WarehouseInterface
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function store(array $data): array
    {
        try {
            $warehouse = Warehouse::create($data);

            return [
                'success' => true,
                'message' => 'Склад успішно створено',
                'warehouse' => $warehouse,
            ];
        } catch (\Exception $e) {
            Log::error('Помилка при створенні складу: ' . $e->getMessage(), [
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Помилка при створенні складу',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Warehouse $warehouse
     * @param Request $request
     *
     * @return array
     */
    public function edit(Warehouse $warehouse, Request $request): array
    {
        try {
            $section = $request->query('section', 'warehouses');
            if ($section !== 'warehouses') {
                return [
                    'success' => false,
                    'message' => 'Такої секції не існує!',
                ];
            }

            $parents = Warehouse::where('id', '!=', $warehouse->id)
                ->orderBy('name')
                ->get(['id', 'name']);

            $managers = User::orderBy('name')->get(['id', 'name']);

            return [
                'success' => true,
                'section' => $section,
                'warehouse' => $warehouse,
                'parents' => $parents,
                'managers' => $managers,
            ];

        } catch (\Exception $e) {
            Log::error('Помилка при редагуванні складу: ' . $e->getMessage(), [
                'warehouse_id' => $warehouse->id,
            ]);

            return [
                'success' => false,
                'message' => 'Помилка при отриманні даних складу',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Warehouse $warehouse
     * @param array $data
     *
     * @return array
     */
    public function update(Warehouse $warehouse, array $data): array
    {
        try {
            $warehouse->update($data);

            return [
                'success'   => true,
                'message'   => 'Склад успішно оновлено',
                'warehouse' => $warehouse,
            ];
        } catch (\Exception $e) {
            Log::error('Помилка при оновленні складу: ' . $e->getMessage(), [
                'warehouse_id' => $warehouse->id,
                'data'         => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Помилка при оновленні складу',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Warehouse $warehouse
     *
     * @return array
     */
    public function destroy(Warehouse $warehouse): array
    {
        try {
            if ($warehouse->children()->exists()) {
                return [
                    'success' => false,
                    'message' => 'Неможливо видалити склад, який має підсклади.',
                ];
            }

            $warehouse->delete();

            return [
                'success' => true,
                'message' => 'Склад успішно видалено.',
            ];
        } catch (\Exception $e) {
            Log::error('Помилка при видаленні складу: ' . $e->getMessage(), [
                'warehouse_id' => $warehouse->id,
            ]);

            return [
                'success' => false,
                'message' => 'Помилка при видаленні складу.',
                'error'   => $e->getMessage(),
            ];
        }
    }
}
