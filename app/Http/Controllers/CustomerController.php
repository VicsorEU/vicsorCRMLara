<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerChannel;
use App\Models\User;
use App\Http\Requests\Customer\StoreRequest;
use App\Http\Requests\Customer\UpdateRequest;
use App\Services\Customer\CustomerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    protected CustomerInterface $customerService;

    public function __construct(CustomerInterface $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * @return View
     */
    public function index(): View
    {
        return view('customers.index');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexAjax(Request $request): JsonResponse
    {
        $res = $this->customerService->renderTable($request);

        return response()->json([
            'success' => $res['success'],
            'html' => $res['html'],
        ]);
    }

    /**
     * @return View
     */
    public function create(): View
    {
        return view('customers.create', [
            'managers' => User::orderBy('name')->get(['id','name']),
            'customer' => new Customer(),
        ]);
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $res = $this->customerService->store($request);

        if ($res['success']) {
            return response()->json([
                'success' => true,
                'message' => $res['message'],
                'customer' => $res['customer'],
                'redirect' => route('customers.show', $res['customer']->id),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $res['message'],
            'error'   => $res['error'] ?? null,
        ], 422);
    }

    /**
     * @param Customer $customer
     *
     * @return View
     */
    public function show(Customer $customer): View
    {
        $customer->load(['manager','channels','addresses']);

        return view('customers.show', compact('customer'));
    }

    /**
     * @param Customer $customer
     *
     * @return View
     */
    public function edit(Customer $customer): View
    {
        $customer->load(['defaultAddress','channels','phones','emails']);

        return view('customers.edit', [
            'customer'=>$customer,
            'managers'=>User::orderBy('name')->get(['id','name']),
        ]);
    }

    /**
     * @param UpdateRequest $request
     * @param Customer $customer
     *
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, Customer $customer): JsonResponse
    {
        $res = $this->customerService->update($customer, $request);

        if ($res['success']) {
            return response()->json([
                'success' => true,
                'message' => $res['message'],
                'customer' => $res['customer'],
                'redirect' => route('customers.show', $res['customer']->id),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $res['message'],
            'error'   => $res['error'] ?? null,
        ], 422);
    }

    /**
     * @param Customer $customer
     *
     * @return JsonResponse
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $res = $this->customerService->destroy($customer);

        return response()->json([
            'success' => $res['success'] ?? false,
            'message' => $res['message'] ?? ($res['success'] ? 'Покупатель удален' : 'Ошибка при удалении'),
        ]);
    }
}
