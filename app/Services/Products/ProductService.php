<?php

namespace App\Services\Products;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductService implements ProductInterface
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function store(array $data): array
    {
        try {
            DB::transaction(function () use ($data, &$product) {
                $product = Product::create([
                    'is_variable' => (bool)($data['is_variable'] ?? false),
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'sku' => $data['sku'] ?? null,
                    'barcode' => $data['barcode'] ?? null,
                    'price_regular' => $data['price_regular'],
                    'price_sale' => $data['price_sale'] ?? null,
                    'weight' => $data['weight'] ?? null,
                    'length' => $data['length'] ?? null,
                    'width' => $data['width'] ?? null,
                    'height' => $data['height'] ?? null,
                    'short_description' => $data['short_description'] ?? null,
                    'description' => $data['description'] ?? null,
                ]);

                // Картинки товара
                foreach (($data['images'] ?? []) as $img) {
                    ProductImage::whereKey($img['id'])->update([
                        'product_id' => $product->id,
                        'variation_id' => null,
                        'is_primary' => !empty($img['is_primary']),
                    ]);
                }

                if (empty($data['is_variable'])) {
                    // Простой товар — сохраняем attribute_values
                    $valueIds = collect($data['attr_pairs'] ?? [])
                        ->pluck('value_id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    $product->attributeValues()->sync($valueIds);

//                previously written code, you may need to
//                $actual = $product->attributeValues()->pluck('attribute_values.id')->all();
                } else {
                    // Вариативный — создаём вариации
                    foreach ($data['variations'] ?? [] as $raw) {
                        $variation = $product->variations()->create([
                            'sku' => $raw['sku'] ?? null,
                            'barcode' => $raw['barcode'] ?? null,
                            'price_regular' => $raw['price_regular'] ?? 0,
                            'price_sale' => $raw['price_sale'] ?? null,
                            'weight' => $raw['weight'] ?? null,
                            'length' => $raw['length'] ?? null,
                            'width' => $raw['width'] ?? null,
                            'height' => $raw['height'] ?? null,
                            'description' => $raw['description'] ?? null,
                        ]);

                        $vValueIds = collect($raw['pairs'] ?? [])
                            ->pluck('value_id')
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();

                        $variation->values()->sync($vValueIds);

                        if (!empty($raw['image_id'])) {
                            ProductImage::whereKey($raw['image_id'])->update([
                                'product_id' => $product->id,
                                'variation_id' => $variation->id,
                                'is_primary' => false,
                            ]);
                        }
                    }
                }
            });

            return [
                'success' => true,
                'message' => 'Товар успішно створено',
                'product' => $product,
            ];

        } catch (\Exception $e) {
            Log::error('Помилка при створенні товару: ' . $e->getMessage(), [
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Помилка при створенні товару',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Product $product
     * @param Request $request
     *
     * @return array
     */
    public function edit(Product $product, Request $request): array
    {
        try {
            $section = $request->query('section', 'products');
            if ($section !== 'products') {
                return [
                    'success' => false,
                    'message' => 'Неверная секция',
                ];
            }

            $values = AttributeValue::with('attribute')
                ->orderBy('attribute_id')
                ->orderBy('name')
                ->get();

            $product->load([
                'images',
                'attributeValues.attribute',
                'variations.values.attribute',
                'variations.image',
            ]);

            $selectedValueIds = $product->attributeValues->pluck('id')->all();

            return [
                'success' => true,
                'section' => $section,
                'product' => $product,
                'values' => $values,
                'selectedValueIds' => $selectedValueIds,
            ];

        } catch (\Exception $e) {
            Log::error('Помилка при редагуванні продукту: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'request' => $request->all(),
            ]);

            return [
                'success' => false,
                'message' => 'Помилка при завантаженні продукту',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Product $product
     * @param array $data
     *
     * @return array
     */
    public function update(Product $product, array $data): array
    {
        try {
            DB::transaction(function () use ($data, $product) {
                $product->update([
                    'is_variable' => (bool)($data['is_variable'] ?? false),
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'sku' => $data['sku'] ?? null,
                    'barcode' => $data['barcode'] ?? null,
                    'price_regular' => $data['price_regular'],
                    'price_sale' => $data['price_sale'] ?? null,
                    'weight' => $data['weight'] ?? null,
                    'length' => $data['length'] ?? null,
                    'width' => $data['width'] ?? null,
                    'height' => $data['height'] ?? null,
                    'short_description' => $data['short_description'] ?? null,
                    'description' => $data['description'] ?? null,
                ]);

                foreach (($data['images'] ?? []) as $img) {
                    ProductImage::whereKey($img['id'])->update([
                        'product_id' => $product->id,
                        'variation_id' => null,
                        'is_primary' => !empty($img['is_primary']),
                    ]);
                }

                if (empty($data['is_variable'])) {
                    // Простой товар
                    $valueIds = collect($data['attr_pairs'] ?? [])
                        ->pluck('value_id')->filter()->unique()->values()->all();

                    $product->attributeValues()->sync($valueIds);

                    $actual = $product->attributeValues()->pluck('attribute_values.id')->all();

                    // Удаляем вариации, если были
                    $product->variations()->each(function ($v) {
                        $v->values()->sync([]);
                        $v->image()?->delete();
                        $v->delete();
                    });
                } else {
                    // Пересоздаём вариации (проще и надёжнее)
                    $product->variations()->each(function ($v) {
                        $v->values()->sync([]);
                        $v->image()?->delete();
                        $v->delete();
                    });

                    foreach ($data['variations'] ?? [] as $raw) {
                        $variation = $product->variations()->create([
                            'sku' => $raw['sku'] ?? null,
                            'barcode' => $raw['barcode'] ?? null,
                            'price_regular' => $raw['price_regular'] ?? 0,
                            'price_sale' => $raw['price_sale'] ?? null,
                            'weight' => $raw['weight'] ?? null,
                            'length' => $raw['length'] ?? null,
                            'width' => $raw['width'] ?? null,
                            'height' => $raw['height'] ?? null,
                            'description' => $raw['description'] ?? null,
                        ]);

                        $vValueIds = collect($raw['pairs'] ?? [])
                            ->pluck('value_id')->filter()->unique()->values()->all();

                        $variation->values()->sync($vValueIds);

                        if (!empty($raw['image_id'])) {
                            ProductImage::whereKey($raw['image_id'])->update([
                                'product_id' => $product->id,
                                'variation_id' => $variation->id,
                                'is_primary' => false,
                            ]);
                        }
                    }

                    $product->attributeValues()->sync([]);
                }
            });

            return [
                'success' => true,
                'message' => 'Товар успішно оновлено',
                'product' => $product->fresh(['images', 'variations.values', 'variations.image']),
            ];
        } catch (\Throwable $e) {
            Log::error('Помилка при оновленні товару: ' . $e->getMessage(), [
                'product_id' => $product->id ?? null,
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Сталася помилка при оновленні товару',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    public function destroy(Product $product): array
    {
        try {
            if (!$product || !$product->exists) {
                return [
                    'success' => false,
                    'message' => 'Товар не знайдено',
                ];
            }

            DB::transaction(function () use ($product) {
                $images = $product->images()->get();
                foreach ($images as $image) {
                    if ($image->path && Storage::disk('public')->exists($image->path)) {
                        Storage::disk('public')->delete($image->path);
                    }
                    $image->delete();
                }

                $variations = $product->variations()->with('values')->get();
                foreach ($variations as $variation) {
                    if ($variation->values()->exists()) {
                        $variation->values()->detach();
                    }
                    $variation->delete();
                }

                if ($product->attributeValues()->exists()) {
                    $product->attributeValues()->detach();
                }

                $product->delete();
            });

            return [
                'success' => true,
                'message' => 'Товар успішно видалено',
            ];

        } catch (\Exception $e) {
            Log::error('Помилка при видаленні товару: ' . $e->getMessage(), [
                'product_id' => $product->id ?? null,
            ]);

            return [
                'success' => false,
                'message' => 'Помилка при видаленні товару',
                'error'   => $e->getMessage(),
            ];
        }
    }
}
