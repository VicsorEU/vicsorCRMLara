<?php

namespace App\Services\Customer;

use App\Http\Requests\Customer\StoreRequest;
use App\Http\Requests\Customer\UpdateRequest;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerService implements CustomerInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function renderTable(Request $request): array
    {
        $search = $request->get('search');

        $items = Customer::query()
            ->with(['manager:id,name', 'defaultAddress', 'phones', 'emails'])
            ->when($search, fn($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('full_name', 'ILIKE', "%$s%")
                    ->orWhereHas('phones', fn($qq) => $qq->where('value', 'ILIKE', "%$s%"))
                    ->orWhereHas('emails', fn($qq) => $qq->where('value', 'ILIKE', "%$s%"));
            }))
            ->orderBy('full_name')
            ->paginate(15)->withQueryString();

        return [
            'success' => true,
            'html' => view('customers._table', ['items' => $items, 'search' => $search])->render(),
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

            DB::transaction(function () use ($data, &$customer) {
                $customer = Customer::create([
                    'full_name' => $data['full_name'],
                    'manager_id' => $data['manager_id'] ?? null,
                    'note' => $data['note'] ?? null,
                    'birth_date' => $data['birth_date'] ?? null,
                ]);

                if (!empty($data['addr'])) {
                    CustomerAddress::create(array_merge($data['addr'], [
                        'customer_id' => $customer->id, 'is_default' => true, 'label' => 'Основной',
                    ]));
                }

                // телефоны
                foreach (($data['phones'] ?? []) as $v) {
                    $v = trim((string)$v);
                    if ($v !== '') $customer->phones()->create(['value' => $v]);
                }
                // e-mail
                foreach (($data['emails'] ?? []) as $v) {
                    $v = trim((string)$v);
                    if ($v !== '') $customer->emails()->create(['value' => $v]);
                }

                // каналы
                foreach (($data['channels'] ?? []) as $ch) {
                    if (!strlen(trim($ch['value'] ?? ''))) continue;
                    $customer->channels()->create(['kind' => $ch['kind'], 'value' => trim($ch['value'])]);
                }
            });

            return [
                'success' => true,
                'customer' => $customer,
                'message' => 'Покупатель успешно создан',
            ];

        } catch (Exception $e) {
            Log::error('Ошибка при создании покупателя', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Произошла ошибка при создании покупателя',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Customer $customer
     * @param UpdateRequest $request
     *
     * @return array
     */
    public function update(Customer $customer, UpdateRequest $request): array
    {
        try {
            $data = $request->validated();

            DB::transaction(function () use ($customer, $data) {
                $customer->update([
                    'full_name' => $data['full_name'],
                    'manager_id' => $data['manager_id'] ?? null,
                    'note' => $data['note'] ?? null,
                    'birth_date' => $data['birth_date'] ?? null,
                ]);

                if (!empty($data['addr'])) {
                    $addr = $customer->defaultAddress()->first();
                    $addr ? $addr->update($data['addr'])
                        : CustomerAddress::create(array_merge($data['addr'], [
                        'customer_id' => $customer->id, 'is_default' => true, 'label' => 'Основной',
                    ]));
                }

                // пересобираем телефоны/почты
                $customer->phones()->delete();
                foreach (($data['phones'] ?? []) as $v) {
                    $v = trim((string)$v);
                    if ($v !== '') $customer->phones()->create(['value' => $v]);
                }

                $customer->emails()->delete();
                foreach (($data['emails'] ?? []) as $v) {
                    $v = trim((string)$v);
                    if ($v !== '') $customer->emails()->create(['value' => $v]);
                }

                $customer->channels()->delete();
                foreach (($data['channels'] ?? []) as $ch) {
                    if (!strlen(trim($ch['value'] ?? ''))) continue;
                    $customer->channels()->create(['kind' => $ch['kind'], 'value' => trim($ch['value'])]);
                }
            });

            return [
                'success' => true,
                'customer' => $customer,
                'message' => 'Покупатель успешно обновлен',
            ];

        } catch (Exception $e) {
            Log::error('Ошибка при обновлении покупателя', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Произошла ошибка при обновлении покупателя',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Customer $customer
     *
     * @return array
     */
    public function destroy(Customer $customer): array
    {
        try {
            $customer->delete();

            return [
                'success' => true,
                'message' => 'Покупатель успешно удален',
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка удаления покупателя: ' . $e->getMessage(), [
                'category_id' => $customer->id,
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при удалении покупателя!',
            ];
        }
    }

}
