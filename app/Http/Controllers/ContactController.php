<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Company;
use App\Http\Requests\Contact\StoreRequest;
use App\Http\Requests\Contact\UpdateRequest;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $r)
    {
        $q = Contact::query()->with('company')
            ->when($r->search, function ($qq, $s) {
                $qq->where(fn($w)=>$w
                    ->where('first_name','ILIKE',"%$s%")
                    ->orWhere('last_name','ILIKE',"%$s%")
                    ->orWhere('email','ILIKE',"%$s%")
                    ->orWhere('phone','ILIKE',"%$s%"));
            })
            ->orderBy('first_name')
            ->paginate(15)->withQueryString();

        return view('contacts.index', ['items'=>$q, 'search'=>$r->search]);
    }

    public function create()
    {
        return view('contacts.create', ['companies'=>Company::orderBy('name')->get(['id','name'])]);
    }

    public function store(StoreRequest $r)
    {
        $data = $r->validated();
        $data['owner_id'] = auth()->id();
        $c = Contact::create($data);
        return redirect()->route('contacts.show', $c)->with('status','Контакт создан');
    }

    public function show(Contact $contact)
    {
        $contact->load('company');
        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        return view('contacts.edit', [
            'contact'=>$contact,
            'companies'=>Company::orderBy('name')->get(['id','name'])
        ]);
    }

    public function update(UpdateRequest $r, Contact $contact)
    {
        $contact->update($r->validated());
        return redirect()->route('contacts.show',$contact)->with('status','Сохранено');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')->with('status','Удалено');
    }
}
