<?php

namespace App\Http\Controllers;

use App\Models\ProductAttribute;
use App\Models\AttributeValue;
use App\Http\Requests\Attribute\StoreRequest;
use App\Http\Requests\Attribute\UpdateRequest;
use App\Services\Attributes\AttributeInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    protected AttributeInterface $attributeService;

    public function __construct(AttributeInterface $attributeService)
    {
        $this->attributeService = $attributeService;
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $res = $this->attributeService->store($data);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return redirect()
            ->route('shops.attribute.edit', [
                'section' => 'attributes',
                'attribute' => $res['attribute'],
            ])
            ->with('status','Атрибут создан');
    }

    public function edit(ProductAttribute $attribute, Request $request)
    {
        $res = $this->attributeService->edit($attribute, $request);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return view('shops.edit', [
            'section'   => $res['section'],
            'attribute' => $attribute->refresh(),
            'parents'   => $res['parents'],
        ]);
    }

    public function update(ProductAttribute $attribute, UpdateRequest $request)
    {
        $res = $this->attributeService->update($attribute, $request);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return redirect()
            ->route('shops.attribute.edit', [
                'section' => 'attributes',
                'attribute' => $attribute->refresh(),
            ])
            ->with('status','Сохранено');
    }

    public function destroy(ProductAttribute $attribute)
    {
        $res = $this->attributeService->destroy($attribute);
        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return redirect()
            ->route('shops.index', [
                'section' => 'attributes',
            ])
            ->with('status','Удалено');
    }
}
