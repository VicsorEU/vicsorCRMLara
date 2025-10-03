<?php

namespace App\Services\Contacts;

use App\Http\Requests\Contact\StoreRequest;
use App\Http\Requests\Contact\UpdateRequest;
use App\Models\Contact;
use Illuminate\Http\Request;

interface ContactInterface
{
    public function renderTable(Request $request);
    public function store(StoreRequest $request);
    public function update(Contact $contact, UpdateRequest $request);
    public function destroy(Contact $contact);
}
