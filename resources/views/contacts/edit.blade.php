@extends('layouts.app')
@section('title','Редактировать контакт — VicsorCRM')
@section('page_title','Редактировать контакт')

@section('content')
    <x-ui.card class="p-6 max-w-3xl">
        <form method="post" action="{{ route('contacts.update', $contact) }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')

            <div>
                <x-ui.label>Имя *</x-ui.label>
                <x-ui.input name="first_name" value="{{ old('first_name', $contact->first_name) }}" required/>
            </div>
            <div>
                <x-ui.label>Фамилия</x-ui.label>
                <x-ui.input name="last_name" value="{{ old('last_name', $contact->last_name) }}"/>
            </div>
            <div>
                <x-ui.label>Email</x-ui.label>
                <x-ui.input type="email" name="email" value="{{ old('email', $contact->email) }}"/>
            </div>
            <div>
                <x-ui.label>Телефон</x-ui.label>
                <x-ui.input name="phone" value="{{ old('phone', $contact->phone) }}"/>
            </div>
            <div>
                <x-ui.label>Должность</x-ui.label>
                <x-ui.input name="position" value="{{ old('position', $contact->position) }}"/>
            </div>
            <div>
                <x-ui.label>Компания</x-ui.label>
                <select name="company_id" class="w-full rounded-xl border px-3 py-2">
                    <option value="">—</option>
                    @foreach($companies as $cmp)
                        <option value="{{ $cmp->id }}" @selected(old('company_id', $contact->company_id) == $cmp->id)>
                            {{ $cmp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <x-ui.label>Заметки</x-ui.label>
                <textarea name="notes" class="w-full rounded-xl border px-3 py-2">{{ old('notes', $contact->notes) }}</textarea>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <x-ui.button>Сохранить</x-ui.button>
                <a href="{{ route('contacts.show', $contact) }}" class="px-4 py-2 rounded-xl border">Отмена</a>

                <form method="post" action="{{ route('contacts.destroy', $contact) }}" class="ml-auto"
                      onsubmit="return confirm('Удалить контакт?');">
                    @csrf
                    @method('DELETE')
                    <x-ui.button variant="light">Удалить</x-ui.button>
                </form>
            </div>
        </form>
    </x-ui.card>
@endsection
