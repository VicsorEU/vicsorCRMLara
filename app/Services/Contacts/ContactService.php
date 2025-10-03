<?php

namespace App\Services\Contacts;

use App\Http\Requests\Contact\StoreRequest;
use App\Http\Requests\Contact\UpdateRequest;
use App\Models\Contact;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactService implements ContactInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function renderTable(Request $request): array
    {
        $search = $request->get('search');

        $items = Contact::query()->with('company')
            ->when($search, function ($qq, $s) {
                $qq->where(fn($w)=>$w
                    ->where('first_name','ILIKE',"%$s%")
                    ->orWhere('last_name','ILIKE',"%$s%")
                    ->orWhere('email','ILIKE',"%$s%")
                    ->orWhere('phone','ILIKE',"%$s%"));
            })
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        return [
            'success' => true,
            'html' => view('contacts._table', compact('items'))->render(),
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
            $contact = Contact::create($data);

            return [
                'success' => true,
                'contact' => $contact,
                'message' => 'Контакт успешно создан',
            ];

        } catch (Exception $e) {
            Log::error('Ошибка при создании контакта', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Произошла ошибка при создании контакта',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Contact $contact
     * @param UpdateRequest $request
     *
     * @return array
     */
    public function update(Contact $contact, UpdateRequest $request): array
    {
        try {
            $data = $request->validated();

            $contact->update($data);

            return [
                'success' => true,
                'contact' => $contact->refresh(),
                'message' => 'Контакт успешно обновлен',
            ];

        } catch (Exception $e) {
            Log::error('Ошибка при обновлении контакт', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Произошла ошибка при обновлении контакта',
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * @param Contact $contact
     *
     * @return array
     */
    public function destroy(Contact $contact): array
    {
        try {
            $contact->delete();

            return [
                'success' => true,
                'message' => 'Контакт успешно удален',
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка удаления контакта: ' . $e->getMessage(), [
                'category_id' => $contact->id,
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при удалении контакта!',
            ];
        }
    }
}
