<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\Company\StoreRequest;
use App\Http\Requests\Company\UpdateRequest;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $r)
    {
        $q = Company::query()
            ->withCount('contacts')
            ->when($r->search, function ($qq, $s) {
                $qq->where(fn($w) => $w
                    ->where('name','ILIKE',"%$s%")
                    ->orWhere('email','ILIKE',"%$s%")
                    ->orWhere('phone','ILIKE',"%$s%"));
            })
            ->orderBy($r->get('sort','name'))
            ->paginate(15)
            ->withQueryString();

        return view('companies.index', ['items'=>$q, 'search'=>$r->search]);
    }

    public function create() { return view('companies.create'); }

    public function store(StoreRequest $r)
    {
        $data = $r->validated();
        $data['owner_id'] = auth()->id();
        $company = Company::create($data);
        return redirect()->route('companies.show',$company)->with('status','Компания создана');
    }

    public function show(Company $company)
    {
        $company->load('contacts');
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company) { return view('companies.edit', compact('company')); }

    public function update(UpdateRequest $r, Company $company)
    {
        $company->update($r->validated());
        return redirect()->route('companies.show',$company)->with('status','Сохранено');
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('companies.index')->with('status','Удалено');
    }
}
