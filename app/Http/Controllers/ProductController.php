<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProductController extends Controller
{
    /** Список товаров */
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q'));

        $products = Product::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'ilike', "%{$q}%")
                    ->orWhere('sku', 'ilike', "%{$q}%")
                    ->orWhere('slug', 'ilike', "%{$q}%");
            })
            ->with(['images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order')])
            ->withCount('variations')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('products.index', compact('products', 'q'));
    }

    /** Форма создания */
    public function create(): View
    {
        $product = new Product();

        // Значения атрибутов с привязкой к атрибутам
        $values = AttributeValue::with('attribute')
            ->orderBy('attribute_id')->orderBy('name')->get();

        return view('products.create', [
            'product' => $product,
            'values'  => $values,
            'action'  => route('products.store'),
            'method'  => 'POST',
        ]);
    }

    /** Сохранение нового */
    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, &$product) {
            $product = Product::create([
                'is_variable'       => (bool)($data['is_variable'] ?? false),
                'name'              => $data['name'],
                'slug'              => $data['slug'],
                'sku'               => $data['sku'] ?? null,
                'barcode'           => $data['barcode'] ?? null,
                'price_regular'     => $data['price_regular'],
                'price_sale'        => $data['price_sale'] ?? null,
                'weight'            => $data['weight'] ?? null,
                'length'            => $data['length'] ?? null,
                'width'             => $data['width'] ?? null,
                'height'            => $data['height'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'description'       => $data['description'] ?? null,
            ]);

            // Картинки товара
            foreach (($data['images'] ?? []) as $img) {
                ProductImage::whereKey($img['id'])->update([
                    'product_id'   => $product->id,
                    'variation_id' => null,
                    'is_primary'   => !empty($img['is_primary']),
                ]);
            }

            if (empty($data['is_variable'])) {
                // Простой товар — сохраняем attribute_values
                $valueIds = collect($data['attr_pairs'] ?? [])
                    ->pluck('value_id')->filter()->unique()->values()->all();

                $product->attributeValues()->sync($valueIds);

                $actual = $product->attributeValues()->pluck('attribute_values.id')->all();
            } else {
                // Вариативный — создаём вариации
                foreach ($data['variations'] ?? [] as $raw) {
                    $variation = $product->variations()->create([
                        'sku'           => $raw['sku'] ?? null,
                        'barcode'       => $raw['barcode'] ?? null,
                        'price_regular' => $raw['price_regular'] ?? 0,
                        'price_sale'    => $raw['price_sale'] ?? null,
                        'weight'        => $raw['weight'] ?? null,
                        'length'        => $raw['length'] ?? null,
                        'width'         => $raw['width'] ?? null,
                        'height'        => $raw['height'] ?? null,
                        'description'   => $raw['description'] ?? null,
                    ]);

                    $vValueIds = collect($raw['pairs'] ?? [])
                        ->pluck('value_id')->filter()->unique()->values()->all();

                    $variation->values()->sync($vValueIds);


                    if (!empty($raw['image_id'])) {
                        ProductImage::whereKey($raw['image_id'])->update([
                            'product_id'   => $product->id,
                            'variation_id' => $variation->id,
                            'is_primary'   => false,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('products.edit', $product)->with('success', 'Товар создан');
    }

    /** Форма редактирования */
    public function edit(Product $product): View
    {
        $values = \App\Models\AttributeValue::with('attribute')
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

        return view('products.edit', [
            'product' => $product,
            'values'  => $values,
            'selectedValueIds' => $selectedValueIds,
            'action'  => route('products.update', $product),
            'method'  => 'PUT',
        ]);
    }


    /** Обновление */
    public function update(UpdateRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $product) {
            $product->update([
                'is_variable'       => (bool)($data['is_variable'] ?? false),
                'name'              => $data['name'],
                'slug'              => $data['slug'],
                'sku'               => $data['sku'] ?? null,
                'barcode'           => $data['barcode'] ?? null,
                'price_regular'     => $data['price_regular'],
                'price_sale'        => $data['price_sale'] ?? null,
                'weight'            => $data['weight'] ?? null,
                'length'            => $data['length'] ?? null,
                'width'             => $data['width'] ?? null,
                'height'            => $data['height'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'description'       => $data['description'] ?? null,
            ]);

            foreach (($data['images'] ?? []) as $img) {
                ProductImage::whereKey($img['id'])->update([
                    'product_id'   => $product->id,
                    'variation_id' => null,
                    'is_primary'   => !empty($img['is_primary']),
                ]);
            }

            if (empty($data['is_variable'])) {
                // Простой — синхронизируем значения
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
                        'sku'           => $raw['sku'] ?? null,
                        'barcode'       => $raw['barcode'] ?? null,
                        'price_regular' => $raw['price_regular'] ?? 0,
                        'price_sale'    => $raw['price_sale'] ?? null,
                        'weight'        => $raw['weight'] ?? null,
                        'length'        => $raw['length'] ?? null,
                        'width'         => $raw['width'] ?? null,
                        'height'        => $raw['height'] ?? null,
                        'description'   => $raw['description'] ?? null,
                    ]);

                    $vValueIds = collect($raw['pairs'] ?? [])
                        ->pluck('value_id')->filter()->unique()->values()->all();

                    $variation->values()->sync($vValueIds);


                    if (!empty($raw['image_id'])) {
                        ProductImage::whereKey($raw['image_id'])->update([
                            'product_id'   => $product->id,
                            'variation_id' => $variation->id,
                            'is_primary'   => false,
                        ]);
                    }
                }

                // Для вариативного товара не держим product_attribute_value
                $product->attributeValues()->sync([]);
            }
        });

        return back()->with('success', 'Товар обновлён');
    }
}
