<?php

namespace App\Http\Controllers;

use App\Models\ProductAttribute;
use App\Models\AttributeValue;
use App\Http\Requests\Attribute\StoreRequest;
use App\Http\Requests\Attribute\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    public function index(Request $r)
    {
        $items = ProductAttribute::query()
            ->withCount('values')
            ->with('parent:id,name')
            ->when($r->search, fn($q,$s)=>$q
                ->where('name','ILIKE',"%$s%")
                ->orWhere('slug','ILIKE',"%$s%"))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('attributes.index', ['items'=>$items, 'search'=>$r->search]);
    }

    public function create()
    {
        return view('attributes.create', [
            'attribute' => new ProductAttribute(),
            'parents'   => ProductAttribute::orderBy('name')->get(['id','name']),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function() use ($data, &$attr) {
            $attr = ProductAttribute::create([
                'name'        => $data['name'],
                'slug'        => $data['slug'],
                'description' => $data['description'] ?? null,
                'parent_id'   => $data['parent_id'] ?? null,
            ]);

            foreach (($data['values'] ?? []) as $row) {
                $name = trim((string)($row['name'] ?? ''));
                $slug = trim((string)($row['slug'] ?? ''));
                if ($name === '' || $slug === '') continue;

                $attr->values()->create([
                    'name' => $name,
                    'slug' => $slug,
                    'sort_order' => (int)($row['sort_order'] ?? 0),
                ]);
            }
        });

        return redirect()->route('attributes.edit', $attr)->with('status','Атрибут создан');
    }

    public function edit(ProductAttribute $attribute)
    {
        $attribute->load('values');
        return view('attributes.edit', [
            'attribute'=>$attribute,
            'parents'  =>ProductAttribute::where('id','!=',$attribute->id)->orderBy('name')->get(['id','name']),
        ]);
    }

    public function update(UpdateRequest $request, ProductAttribute $attribute)
    {
        $data = $request->validated();

        DB::transaction(function() use ($attribute,$data) {
            $attribute->fill([
                'name'        => $data['name'],
                'slug'        => $data['slug'],
                'description' => $data['description'] ?? null,
                'parent_id'   => $data['parent_id'] ?? null,
            ])->save();

            $rows = collect($data['values'] ?? []);

            // ids, которые пришли — оставляем, остальные удалим
            $keepIds = $rows->pluck('id')->filter()->map(fn($v)=>(int)$v)->all();
            AttributeValue::where('attribute_id',$attribute->id)
                ->when($keepIds, fn($q)=>$q->whereNotIn('id',$keepIds))
                ->delete();

            foreach ($rows as $row) {
                $name = trim((string)($row['name'] ?? ''));
                $slug = trim((string)($row['slug'] ?? ''));
                $order= (int)($row['sort_order'] ?? 0);
                $id   = isset($row['id']) ? (int)$row['id'] : null;

                if ($name === '' || $slug === '') continue;

                if ($id) {
                    AttributeValue::where('id',$id)
                        ->where('attribute_id',$attribute->id)
                        ->update(['name'=>$name,'slug'=>$slug,'sort_order'=>$order]);
                } else {
                    $attribute->values()->create(['name'=>$name,'slug'=>$slug,'sort_order'=>$order]);
                }
            }
        });

        return redirect()->route('attributes.edit',$attribute)->with('status','Сохранено');
    }

    public function destroy(ProductAttribute $attribute)
    {
        $attribute->delete();
        return redirect()->route('attributes.index')->with('status','Удалено');
    }
}
