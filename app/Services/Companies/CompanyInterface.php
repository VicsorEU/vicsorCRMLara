<?php

namespace App\Services\Companies;

use App\Http\Requests\Company\StoreRequest;
use App\Http\Requests\Company\UpdateRequest;
use App\Models\Company;
use Illuminate\Http\Request;

interface CompanyInterface
{
    public function renderTable(Request $request): array;
    public function store(StoreRequest $request): array;

    public function update(Company $company, UpdateRequest $request): array;
    public function destroy(Company $company): array;
}
