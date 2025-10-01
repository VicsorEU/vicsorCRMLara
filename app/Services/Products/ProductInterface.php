<?php

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Http\Request;

interface ProductInterface
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function store(array $data): array;

    /**
     * @param Product $product
     * @param Request $request
     *
     * @return array
     */
    public function edit(Product $product, Request $request): array;

    /**
     * @param Product $product
     * @param array $data
     *
     * @return array
     */
    public function update(Product $product, array $data): array;
    public function destroy();
}
