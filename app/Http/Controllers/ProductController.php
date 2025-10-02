<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Product;
use App\Services\Products\ProductInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductInterface $productService;

    public function __construct(ProductInterface $productService)
    {
        $this->productService = $productService;
    }
    /** Список товаров */
    public function index()
    {
    //
    }

    /** Форма создания */
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

        $res = $this->productService->store($data);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return redirect()->route('shops.product.edit', ['section' => 'products', 'product' => $res['product']])->with('success', 'Товар создан');
    }

    /**
     * @param Product $product
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|RedirectResponse|object
     */
    public function edit(Product $product, Request $request): mixed
    {
        $res = $this->productService->edit($product, $request);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return view('shops.edit', [
            'section' => $res['section'],
            'product' => $res['product'],
            'values' => $res['values'],
            'selectedValueIds' => $res['selectedValueIds'],
        ]);
    }


    /**
     * @param Product $product
     * @param UpdateRequest $request
     *
     * @return RedirectResponse
     */
    public function update(Product $product, UpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $res = $this->productService->edit($product, $data);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return back()->with('success', 'Товар обновлён');
    }

    public function destroy(Product $product)
    {
        $res = $this->productService->destroy($product);
        if (!$res['success']) {
            return response()->json([
                'success' => false,
                'message' => $res['message'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Продукт удален успешно',
        ]);
    }
}
