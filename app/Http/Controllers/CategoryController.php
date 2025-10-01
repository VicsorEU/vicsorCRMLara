<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Services\Categories\CategoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected CategoryInterface $categoryService;

    public function __construct(CategoryInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $r)
    {
        $items = Category::query()
            ->with('parent:id,name')
            ->when($r->search, fn($q,$s)=>$q->where('name','ILIKE',"%$s%")->orWhere('slug','ILIKE',"%$s%"))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('categories.index', ['items'=>$items, 'search'=>$r->search]);
    }

    public function create()
    {
        return view('categories.create', [
            'category' => new Category(),
            'parents'  => Category::orderBy('name')->get(['id','name']),
        ]);
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category): RedirectResponse
    {
        $res = $this->categoryService->destroy($category);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return redirect()->route('shops.index', ['section' => 'categories'])->with('status','Категория удалена');
    }
}
