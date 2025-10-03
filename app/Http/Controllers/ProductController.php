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

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        $res = $this->productService->store($data);

        return response()->json([
            'success'   => $res['success'],
            'message'   => $res['message'],
            'product' => $res['product'] ?? null,
        ]);
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

    public function update(Product $product, UpdateRequest $request)
    {
        $data = $request->validated();

        $res = $this->productService->update($product, $data);

        return response()->json([
            'success'   => $res['success'],
            'message'   => $res['message'],
            'product' => $res['product'] ?? null,
        ]);
    }

    public function destroy(Product $product)
    {
        $res = $this->productService->destroy($product);

        return response()->json([
            'success'   => $res['success'],
            'message'   => $res['message'],
        ]);
    }
}
