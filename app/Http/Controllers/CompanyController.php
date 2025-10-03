<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\Company\StoreRequest;
use App\Http\Requests\Company\UpdateRequest;
use App\Services\Companies\CompanyInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    protected CompanyInterface $companyService;

    public function __construct(CompanyInterface $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * @return View
     */
    public function index(): View
    {
        return view('companies.index');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAjax(Request $request): JsonResponse
    {
        $res = $this->companyService->renderTable($request);

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
        return view('companies.create');
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $res = $this->companyService->store($request);

        if ($res['success']) {
            return response()->json([
                'success' => true,
                'message' => $res['message'],
                'company' => $res['company'],
                'redirect' => route('companies.show', $res['company']->id),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $res['message'],
            'error'   => $res['error'] ?? null,
        ], 422);
    }

    /**
     * @param Company $company
     *
     * @return View
     */
    public function show(Company $company): View
    {
        $company->load('contacts');
        return view('companies.show', compact('company'));
    }

    /**
     * @param Company $company
     *
     * @return View
     */
    public function edit(Company $company): View
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * @param UpdateRequest $request
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, Company $company): JsonResponse
    {
        $res = $this->companyService->update($company, $request);

        if ($res['success']) {
            return response()->json([
                'success' => true,
                'message' => $res['message'],
                'company' => $res['company'],
                'redirect' => route('companies.show', $res['company']->id),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $res['message'],
            'error'   => $res['error'] ?? null,
        ], 422);
    }

    /**
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function destroy(Company $company): JsonResponse
    {
        $res = $this->companyService->destroy($company);

        return response()->json([
            'success' => $res['success'] ?? false,
            'message' => $res['message'] ?? ($res['success'] ? 'Компания удалена' : 'Ошибка при удалении'),
        ]);
    }
}
