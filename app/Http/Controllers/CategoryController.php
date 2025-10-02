<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Services\Categories\CategoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected CategoryInterface $categoryService;

    public function __construct(CategoryInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
     //
    }

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
        $res = $this->categoryService->store($request);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return redirect()
            ->route('shops.category.edit', ['section' => 'categories', 'category' => $res['category']])
            ->with('status','Категория создана');
    }

    /**
     * @param Category $category
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|RedirectResponse|object
     */
    public function edit(Category $category, Request $request): mixed
    {
        $res = $this->categoryService->edit($category, $request);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return view('shops.edit', [
            'section'  => $res['section'],
            'category' => $category->refresh(),
            'parents'  => $res['parents'],
        ]);
    }

    /**
     * @param Category $category
     * @param UpdateRequest $request
     *
     * @return RedirectResponse
     */
    public function update(Category $category, UpdateRequest $request): RedirectResponse
    {
        $res = $this->categoryService->update($category, $request);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return redirect()->route('shops.category.edit', ['section' => 'categories', 'category' => $category])->with('status','Сохранено');
    }

    /**
     * @param Category $category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category): JsonResponse
    {
        $res = $this->categoryService->destroy($category);
        if (!$res['success']) {
            return response()->json([
                'success' => false,
                'message' => $res['message'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Категория удален успешно',
        ]);
    }
}
