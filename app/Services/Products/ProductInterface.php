<?php

namespace App\Services\Products;

use App\Http\Requests\Product\UpdateRequest;
use App\Models\Product;
use Illuminate\Http\Request;

interface ProductInterface
{
    public function store(array $data);
    public function edit(Product $product, Request $request);
    public function update(Product $product, UpdateRequest $request);
    public function destroy();
}
