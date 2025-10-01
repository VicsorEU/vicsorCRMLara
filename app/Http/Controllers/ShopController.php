<?php

namespace App\Http\Controllers;

use App\Services\Shops\ShopInterface;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    protected ShopInterface $shopService;

    public function __construct(ShopInterface $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|object
     */
    public function index(Request $request): mixed
    {
        $res = $this->shopService->index($request);

        return view('shops.index', [
            'section' => $res['section'],
            'items'   => $res['items'],
            'search'  => $res['search'],
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|object
     */
    public function create(Request $request): mixed
    {
        $res = $this->shopService->create($request);

        if (!$res['success']) {
            return back()->withErrors($res['message']);
        }

        return view('shops.create', $res);
    }
}
