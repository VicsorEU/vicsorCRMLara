<?php

namespace App\Services\Companies;

use App\Http\Requests\Company\StoreRequest;
use App\Http\Requests\Company\UpdateRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CompanyService implements CompanyInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function renderTable(Request $request): array
    {
        $search = $request->get('search');

        $items = Company::query()
            ->withCount('contacts')
            ->when($search, function ($qq, $s) {
                $qq->where(fn($w) => $w
                    ->where('name','ILIKE',"%$s%")
                    ->orWhere('email','ILIKE',"%$s%")
                    ->orWhere('phone','ILIKE',"%$s%"));
            })
            ->orderBy($request->get('sort','name'))
            ->paginate(15)
            ->withQueryString();

        return [
            'success' => true,
            'html' => view('companies._table', compact('items'))->render(),
        ];
    }

    /**
     * @param StoreRequest $request
     *
     * @return array
     */
    public function store(StoreRequest $request): array
    {
        try {
            $data = $request->validated();

            $data['owner_id'] = auth()->id();

            $company = Company::create($data);

            return [
                'success' => true,
                'company' => $company,
                'message' => 'Компания успешно создана',
            ];

        } catch (Exception $e) {
            Log::error('Ошибка при создании компании', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Произошла ошибка при создании компании',
                'error'   => $e->getMessage(),
            ];
        }
    }

    public function update(Company $company, UpdateRequest $request): array
    {
        try {
            $data = $request->validated();

            $company->update($data);

            return [
                'success' => true,
                'company' => $company->refresh(),
                'message' => 'Компания успешно обновлена',
            ];

        } catch (Exception $e) {
            Log::error('Ошибка при обновлении компании', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Произошла ошибка при обновлении компании',
                'error'   => $e->getMessage(),
            ];
        }
    }

    public function destroy(Company $company): array
    {
        try {
            $company->delete();

            return [
                'success' => true,
                'message' => 'Компания успешно удалена',
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка удаления компании: ' . $e->getMessage(), [
                'category_id' => $company->id,
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при удалении компании!',
            ];
        }
    }
}
