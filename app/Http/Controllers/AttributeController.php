<?php

namespace App\Http\Controllers;

use App\Models\ProductAttribute;
use App\Models\AttributeValue;
use App\Http\Requests\Attribute\StoreRequest;
use App\Http\Requests\Attribute\UpdateRequest;
use App\Services\Attributes\AttributeInterface;
use Illuminate\Http\JsonResponse;
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
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $res = $this->attributeService->store($data);

        return response()->json([
            'success'   => $res['success'],
            'message'   => $res['message'],
            'attribute' => $res['attribute'] ?? null,
        ]);
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

    /**
     * @param ProductAttribute $attribute
     * @param UpdateRequest $request
     *
     * @return JsonResponse
     */
    public function update(ProductAttribute $attribute, UpdateRequest $request): JsonResponse
    {
        $res = $this->attributeService->update($attribute, $request);

        return response()->json([
            'success'   => $res['success'],
            'message'   => $res['message'],
            'attribute' => $res['attribute'] ?? null,
        ]);
    }

    /**
     * @param ProductAttribute $attribute
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ProductAttribute $attribute): JsonResponse
    {
        $res = $this->attributeService->destroy($attribute);
        if (!$res['success']) {
            return response()->json([
                'success' => false,
                'message' => $res['message'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Атрибут удален успешно',
        ]);
    }
}
