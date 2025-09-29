<?php

namespace App\Http\Controllers;

use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $section = $request->query('section', 'products');
        $search = trim((string)$request->get('search'));

        $allowedSections = ['categories', 'attributes', 'warehouses', 'products'];
        if (!in_array($section, $allowedSections, true)) {
            $section = 'products';
        }

        switch ($section) {

            case 'products':
                $products = Product::query()
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

                return view('shops.index', compact('section', 'products', 'search'));

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

                return view('shops.index', compact('section', 'items', 'search'));

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

                return view('shops.index', compact('section', 'items', 'search'));

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

                return view('shops.index', compact('section', 'items', 'search'));
        }

        return view('shops.index', compact('section'));
    }

    public function create(Request $request)
    {
        $section = $request->query('section', 'products');

        switch ($section) {
            case 'category':
                $category = new Category();
                $parents = Category::orderBy('name')->get(['id', 'name']);

                return view('shops.create', compact('section', 'category', 'parents'));

            case 'attribute':
                $attribute = new ProductAttribute();
                $parents = ProductAttribute::orderBy('name')->get(['id', 'name']);

                return view('shops.create', compact('section', 'attribute', 'parents'));

            case 'warehouse':
                $warehouse = new Warehouse();
                $parents = Warehouse::orderBy('name')->get(['id', 'name']);
                $managers = User::orderBy('name')->get(['id', 'name']);

                return view('shops.create', compact('section', 'warehouse', 'parents', 'managers'));

            case 'product':
            default:
                $product = new Product();
                $values = AttributeValue::with('attribute')->orderBy('attribute_id')->orderBy('name')->get();

                return view('shops.create', compact('section', 'product', 'values'));
        }
    }
}
