<?php

namespace App\Services\Customer;

use App\Http\Requests\Customer\StoreRequest;
use App\Http\Requests\Customer\UpdateRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

interface CustomerInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function renderTable(Request $request): array;

    /**
     * @param StoreRequest $request
     *
     * @return array
     */
    public function store(StoreRequest $request): array;

    /**
     * @param Customer $customer
     * @param UpdateRequest $request
     *
     * @return array
     */
    public function update(Customer $customer, UpdateRequest $request): array;

    /**
     * @param Customer $customer
     *
     * @return array
     */
    public function destroy(Customer $customer): array;
}
