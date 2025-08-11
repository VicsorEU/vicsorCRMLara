<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerChannel;
use App\Models\User;
use App\Http\Requests\Customer\StoreRequest;
use App\Http\Requests\Customer\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $r)
    {
        $items = Customer::query()
            ->with(['manager:id,name','defaultAddress','phones','emails'])
            ->when($r->search, fn($q,$s)=>$q->where(function($w) use($s){
                $w->where('full_name','ILIKE',"%$s%")
                    ->orWhereHas('phones', fn($qq)=>$qq->where('value','ILIKE',"%$s%"))
                    ->orWhereHas('emails', fn($qq)=>$qq->where('value','ILIKE',"%$s%"));
            }))
            ->orderBy('full_name')
            ->paginate(15)->withQueryString();

        return view('customers.index', ['items'=>$items, 'search'=>$r->search]);
    }

    public function create()
    {
        return view('customers.create', [
            'managers' => User::orderBy('name')->get(['id','name']),
            'customer' => new Customer(),
        ]);
    }

    public function store(StoreRequest $r)
    {
        $data = $r->validated();

        DB::transaction(function() use ($data, &$customer) {
            $customer = Customer::create([
                'full_name' => $data['full_name'],
                'manager_id'=> $data['manager_id'] ?? null,
                'note'      => $data['note'] ?? null,
                'birth_date'=> $data['birth_date'] ?? null,
            ]);

            if (!empty($data['addr'])) {
                CustomerAddress::create(array_merge($data['addr'], [
                    'customer_id'=>$customer->id, 'is_default'=>true, 'label'=>'Основной',
                ]));
            }

            // телефоны
            foreach (($data['phones'] ?? []) as $v) {
                $v = trim((string)$v);
                if ($v !== '') $customer->phones()->create(['value'=>$v]);
            }
            // e-mail
            foreach (($data['emails'] ?? []) as $v) {
                $v = trim((string)$v);
                if ($v !== '') $customer->emails()->create(['value'=>$v]);
            }

            // каналы
            foreach (($data['channels'] ?? []) as $ch) {
                if (!strlen(trim($ch['value'] ?? ''))) continue;
                $customer->channels()->create(['kind'=>$ch['kind'], 'value'=>trim($ch['value'])]);
            }
        });

        return redirect()->route('customers.show', $customer)->with('status','Покупатель создан');
    }

    public function show(Customer $customer)
    {
        $customer->load(['manager','channels','addresses']);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $customer->load(['defaultAddress','channels','phones','emails']);
        return view('customers.edit', [
            'customer'=>$customer,
            'managers'=>User::orderBy('name')->get(['id','name']),
        ]);
    }

    public function update(UpdateRequest $r, Customer $customer)
    {
        $data = $r->validated();

        DB::transaction(function() use ($customer,$data) {
            $customer->update([
                'full_name' => $data['full_name'],
                'manager_id'=> $data['manager_id'] ?? null,
                'note'      => $data['note'] ?? null,
                'birth_date'=> $data['birth_date'] ?? null,
            ]);

            if (!empty($data['addr'])) {
                $addr = $customer->defaultAddress()->first();
                $addr ? $addr->update($data['addr'])
                    : CustomerAddress::create(array_merge($data['addr'], [
                    'customer_id'=>$customer->id,'is_default'=>true,'label'=>'Основной',
                ]));
            }

            // пересобираем телефоны/почты
            $customer->phones()->delete();
            foreach (($data['phones'] ?? []) as $v) {
                $v = trim((string)$v);
                if ($v !== '') $customer->phones()->create(['value'=>$v]);
            }

            $customer->emails()->delete();
            foreach (($data['emails'] ?? []) as $v) {
                $v = trim((string)$v);
                if ($v !== '') $customer->emails()->create(['value'=>$v]);
            }

            $customer->channels()->delete();
            foreach (($data['channels'] ?? []) as $ch) {
                if (!strlen(trim($ch['value'] ?? ''))) continue;
                $customer->channels()->create(['kind'=>$ch['kind'],'value'=>trim($ch['value'])]);
            }
        });

        return redirect()->route('customers.show',$customer)->with('status','Сохранено');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('status','Удалено');
    }
}
