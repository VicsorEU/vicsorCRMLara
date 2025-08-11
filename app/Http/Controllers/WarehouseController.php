<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\User;
use App\Http\Requests\Warehouse\StoreRequest;
use App\Http\Requests\Warehouse\UpdateRequest;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $r)
    {
        // забираем все, чтобы построить дерево на фронте
        $items = Warehouse::query()
            ->withCount('children')
            ->orderBy('parent_id')->orderBy('sort_order')->orderBy('name')
            ->get();

        // для поиска оставим простую фильтрацию по имени/коду
        $search = trim((string)$r->search);
        if ($search !== '') {
            $items = $items->filter(fn($w) =>
                str_contains(mb_strtolower($w->name), mb_strtolower($search)) ||
                str_contains(mb_strtolower($w->code), mb_strtolower($search))
            );
        }

        return view('warehouses.index', compact('items','search'));
    }

    public function create()
    {
        return view('warehouses.create', [
            'warehouse' => new Warehouse(),
            'parents'   => Warehouse::orderBy('name')->get(['id','name']),
            'managers'  => User::orderBy('name')->get(['id','name']),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $w = Warehouse::create($request->validated());
        return redirect()->route('warehouses.edit',$w)->with('status','Склад создан');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', [
            'warehouse'=>$warehouse,
            'parents'  =>Warehouse::where('id','!=',$warehouse->id)->orderBy('name')->get(['id','name']),
            'managers' =>User::orderBy('name')->get(['id','name']),
        ]);
    }

    public function update(UpdateRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());
        return redirect()->route('warehouses.edit',$warehouse)->with('status','Сохранено');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->children()->exists()) {
            return back()->withErrors('Нельзя удалить склад с подскладами.');
        }
        $warehouse->delete();
        return redirect()->route('warehouses.index')->with('status','Удалено');
    }
}
