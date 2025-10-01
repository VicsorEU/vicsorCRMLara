<?php

namespace App\Services\Attributes;

use App\Http\Requests\Attribute\StoreRequest;
use App\Http\Requests\Attribute\UpdateRequest;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;

interface AttributeInterface
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function store(array $data): array;

    /**
     * @param ProductAttribute $attribute
     * @param Request $request
     *
     * @return mixed
     */
    public function edit(ProductAttribute $attribute, Request $request): array;

    /**
     * @param UpdateRequest $request
     * @param ProductAttribute $attribute
     *
     * @return array
     */
    public function update(ProductAttribute $attribute, UpdateRequest $request): array;

    /**
     * @param ProductAttribute $attribute
     *
     * @return array
     */
    public function destroy(ProductAttribute $attribute): array;
}
