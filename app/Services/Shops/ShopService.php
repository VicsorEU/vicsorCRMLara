<?php

namespace App\Services\Shops;

use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class ShopService implements ShopInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request): array
    {
        $section = $request->query('section', 'products');
        $search = trim((string)$request->get('search'));

        $allowedSections = ['categories', 'attributes', 'warehouses', 'products'];
        if (!in_array($section, $allowedSections, true)) {
            $section = 'products';
        }

        $items = null;

        switch ($section) {
            case 'products':
                $items = Product::query()
                    ->when($search, fn($query) => $query
                        ->where('name', 'ilike', "%{$search}%")
                        ->orWhere('sku', 'ilike', "%{$search}%")
                        ->orWhere('slug', 'ilike', "%{$search}%")
                    )
                    ->with(['images' => fn($q) => $q->orderByDesc('is_primary')->orderBy('sort_order')])
                    ->withCount('variations')
                    ->orderByDesc('id')
                    ->paginate(20)
                    ->withQueryString();
                break;

            case 'attributes':
                $items = ProductAttribute::query()
                    ->withCount('values')
                    ->with('parent:id,name')
                    ->when($search, fn($q, $s) => $q
                        ->where('name', 'ILIKE', "%$s%")
                        ->orWhere('slug', 'ILIKE', "%$s%")
                    )
                    ->orderBy('name')
                    ->paginate(15)
                    ->withQueryString();
                break;

            case 'warehouses':
                $items = Warehouse::query()
                    ->withCount('children')
                    ->orderBy('parent_id')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();

                if ($search !== '') {
                    $searchLower = mb_strtolower($search);
                    $items = $items->filter(fn($w) => str_contains(mb_strtolower($w->name), $searchLower) ||
                        str_contains(mb_strtolower($w->code), $searchLower)
                    );
                }
                break;

            case 'categories':
                $items = Category::query()
                    ->with('parent:id,name')
                    ->when($search, fn($q, $s) => $q
                        ->where('name', 'ILIKE', "%$s%")
                        ->orWhere('slug', 'ILIKE', "%$s%")
                    )
                    ->orderBy('name')
                    ->paginate(15)
                    ->withQueryString();
                break;
        }

        return [
            'items' => $items,
            'section' => $section,
            'search' => $search,
        ];
    }

    /**
     * @param string $section
     * @param Collection $items
     *
     * @return string
     */
    public function renderTable(string $section, $items): string
    {
        $itemsCollection = $items instanceof LengthAwarePaginator ? collect($items->items()) : $items;

        switch ($section) {
            case 'warehouses':
                $groups = $itemsCollection->groupBy(fn($w) => $w->parent_id ?? 0);
                $roots  = $groups->get(0, collect());
                return view('shops.warehouses._table', compact('roots', 'groups'))->render();

            case 'products':
                // передаємо сам пагінатор у Blade, щоб працювала пагінація
                return view('shops.products._table', ['items' => $items])->render();

            case 'categories':
                return view('shops.categories._table', ['items' => $items])->render();

            case 'attributes':
                return view('shops.attributes._table', ['items' => $items])->render();

            default:
                return '<div class="text-red-500">Раздел не найден</div>';
        }
    }


    /**
     * @param Request $request
     *
     * @return array
     */
    public function create(Request $request): array
    {
        try {
            $section = $request->query('section', 'product');

            switch ($section) {
                case 'category':
                    return [
                        'success' => true,
                        'section' => $section,
                        'category' => new Category(),
                        'parents' => Category::orderBy('name')->get(['id', 'name']),
                    ];

                case 'attribute':
                    return [
                        'success' => true,
                        'section' => $section,
                        'attribute' => new ProductAttribute(),
                        'parents' => ProductAttribute::orderBy('name')->get(['id', 'name']),
                    ];

                case 'warehouse':
                    return [
                        'success' => true,
                        'section' => $section,
                        'warehouse' => new Warehouse(),
                        'parents' => Warehouse::orderBy('name')->get(['id', 'name']),
                        'managers' => User::orderBy('name')->get(['id', 'name']),
                    ];

                case 'product':
                default:
                    return [
                        'success' => true,
                        'section' => 'product',
                        'product' => new Product(),
                        'values' => AttributeValue::with('attribute')
                            ->orderBy('attribute_id')
                            ->orderBy('name')
                            ->get(),
                    ];
            }
        } catch (\Exception $e) {
            Log::error("Помилка при створенні форми: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Не вдалося відкрити форму створення',
                'error'   => $e->getMessage(),
            ];
        }
    }
}
