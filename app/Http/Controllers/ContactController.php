<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Company;
use App\Http\Requests\Contact\StoreRequest;
use App\Http\Requests\Contact\UpdateRequest;
use App\Services\Contacts\ContactInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    protected ContactInterface $contactService;

    public function __construct(ContactInterface $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * @return View
     */
    public function index(): View
    {
        return view('contacts.index');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexAjax(Request $request): JsonResponse
    {
        $res = $this->contactService->renderTable($request);

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
        return view('contacts.create', ['companies'=>Company::orderBy('name')->get(['id','name'])]);
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $res = $this->contactService->store($request);

        if ($res['success']) {
            return response()->json([
                'success' => true,
                'message' => $res['message'],
                'contact' => $res['contact'],
                'redirect' => route('contacts.show', $res['contact']->id),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $res['message'],
            'error'   => $res['error'] ?? null,
        ], 422);
    }

    /**
     * @param Contact $contact
     *
     * @return View
     */
    public function show(Contact $contact): View
    {
        $contact->load('company');
        return view('contacts.show', compact('contact'));
    }

    /**
     * @param Contact $contact
     *
     * @return View
     */
    public function edit(Contact $contact): View
    {
        return view('contacts.edit', [
            'contact'=>$contact,
            'companies'=>Company::orderBy('name')->get(['id','name'])
        ]);
    }

    /**
     * @param UpdateRequest $request
     * @param Contact $contact
     *
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, Contact $contact): JsonResponse
    {
        $res = $this->contactService->update($contact, $request);

        if ($res['success']) {
            return response()->json([
                'success' => true,
                'message' => $res['message'],
                'contact' => $res['contact'],
                'redirect' => route('contacts.show', $res['contact']->id),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $res['message'],
            'error'   => $res['error'] ?? null,
        ], 422);
    }

    /**
     * @param Contact $contact
     *
     * @return JsonResponse
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $res = $this->contactService->destroy($contact);

        return response()->json([
            'success' => $res['success'] ?? false,
            'message' => $res['message'] ?? ($res['success'] ? 'Контакт удален' : 'Ошибка при удалении'),
        ]);
    }
}
