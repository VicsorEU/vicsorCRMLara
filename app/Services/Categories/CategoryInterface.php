<?php

namespace App\Services\Categories;

use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Models\Category;
use Illuminate\Http\Request;

interface CategoryInterface
{
    /**
     * @param StoreRequest $request
     *
     * @return array
     */
    public function store(StoreRequest $request): array;

    /**
     * @param Category $category
     * @param Request $request
     *
     * @return array
     */
    public function edit(Category $category, Request $request): array;

    /**
     * @param UpdateRequest $request
     * @param Category $category
     *
     * @return Category
     */
    public function update(Category $category, UpdateRequest $request): array;

    /**
     * @param Category $category
     *
     * @return array
     */
    public function destroy(Category $category): array;
}
