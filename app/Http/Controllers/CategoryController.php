<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
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

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create([
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
            'parent_id'   => $data['parent_id'] ?? null,
            'image_path'  => $imagePath,
        ]);

        return redirect()->route('categories.edit', $category)->with('status','Категория создана');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', [
            'category'=>$category,
            'parents' =>Category::where('id','!=',$category->id)->orderBy('name')->get(['id','name']),
        ]);
    }

    public function update(UpdateRequest $request, Category $category)
    {
        $data = $request->validated();

        // удаление изображения по флажку
        if ($request->boolean('remove_image') && $category->image_path) {
            Storage::disk('public')->delete($category->image_path);
            $category->image_path = null;
        }

        // замена изображения
        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $category->image_path = $request->file('image')->store('categories', 'public');
        }

        $category->fill([
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
            'parent_id'   => $data['parent_id'] ?? null,
        ])->save();

        return redirect()->route('categories.edit',$category)->with('status','Сохранено');
    }

    public function destroy(Category $category)
    {
        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }
        $category->delete();
        return redirect()->route('categories.index')->with('status','Категория удалена');
    }
}
