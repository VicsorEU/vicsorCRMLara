<?php

namespace App\Services\Attributes;

use App\Http\Requests\Attribute\UpdateRequest;
use App\Models\AttributeValue;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttributeService implements AttributeInterface
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function store(array $data): array
    {
        try {
            $attr = null;

            DB::transaction(function () use ($data, &$attr) {
                $attr = ProductAttribute::create([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'parent_id' => $data['parent_id'] ?? null,
                ]);

                foreach (($data['values'] ?? []) as $row) {
                    $name = trim((string)($row['name'] ?? ''));
                    $slug = trim((string)($row['slug'] ?? ''));

                    if ($name === '' || $slug === '') continue;

                    $attr->values()->create([
                        'name' => $name,
                        'slug' => $slug,
                        'sort_order' => (int)($row['sort_order'] ?? 0),
                    ]);
                }
            });

            return [
                'success' => true,
                'message' => 'Атрибут успешно создан',
                'attribute' => $attr,
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка создания атрибута: ' . $e->getMessage(), [
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при создании атрибута',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param ProductAttribute $attribute
     * @param Request $request
     *
     * @return array
     */
    public function edit(ProductAttribute $attribute, Request $request): array
    {
        try {
            $section = $request->query('section', 'attributes');

            if ($section !== 'attributes') {
                return [
                    'success' => false,
                    'message' => 'Такой секции не существует!',
                ];
            }

            $attribute->load('values');

            $parents = ProductAttribute::where('id', '!=', $attribute->id)
                ->orderBy('name')
                ->get(['id', 'name']);

            return [
                'success' => true,
                'section' => $section,
                'attribute' => $attribute,
                'parents' => $parents,
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка при получении атрибута для редактирования: ' . $e->getMessage(), [
                'attribute_id' => $attribute->id,
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при получении данных атрибута',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function update(ProductAttribute $attribute, UpdateRequest $request): array
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($attribute, $data) {
                $attribute->fill([
                    'name'        => $data['name'],
                    'slug'        => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'parent_id'   => $data['parent_id'] ?? null,
                ])->save();

                $rows = collect($data['values'] ?? []);

                // ids, которые пришли — оставляем, остальные удалим
                $keepIds = $rows->pluck('id')->filter()->map(fn($v) => (int)$v)->all();
                AttributeValue::where('attribute_id', $attribute->id)
                    ->when($keepIds, fn($q) => $q->whereNotIn('id', $keepIds))
                    ->delete();

                foreach ($rows as $row) {
                    $name = trim((string)($row['name'] ?? ''));
                    $slug = trim((string)($row['slug'] ?? ''));
                    $order = (int)($row['sort_order'] ?? 0);
                    $id = isset($row['id']) ? (int)$row['id'] : null;

                    if ($name === '' || $slug === '') continue;

                    if ($id) {
                        AttributeValue::where('id', $id)
                            ->where('attribute_id', $attribute->id)
                            ->update([
                                'name' => $name,
                                'slug' => $slug,
                                'sort_order' => $order,
                            ]);
                    } else {
                        $attribute->values()->create([
                            'name' => $name,
                            'slug' => $slug,
                            'sort_order' => $order,
                        ]);
                    }
                }
            });

            return [
                'success' => true,
                'message' => 'Атрибут успішно оновлено',
                'attribute' => $attribute->refresh()->load('values'),
            ];

        } catch (\Exception $e) {
            Log::error('Помилка оновлення атрибута: ' . $e->getMessage(), [
                'attribute_id' => $attribute->id,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Помилка при оновленні атрибута',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function destroy(ProductAttribute $attribute): array
    {
        try {
            $attribute->delete();

            return [
                'success' => true,
                'message' => 'Атрибут успішно видалено',
            ];
        } catch (\Exception $e) {
            Log::error('Помилка при видаленні атрибута: ' . $e->getMessage(), [
                'attribute_id' => $attribute->id,
            ]);

            return [
                'success' => false,
                'message' => 'Помилка при видаленні атрибута',
                'error' => $e->getMessage(),
            ];
        }
    }

}
