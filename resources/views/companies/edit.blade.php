@extends('layouts.app')
@section('title','Редактировать компанию — VicsorCRM')
@section('page_title','Редактировать компанию')

@section('content')
    <x-ui.card class="p-6 max-w-3xl">
        <form method="post" action="{{ route('companies.update', $company) }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')

            <div class="md:col-span-2">
                <x-ui.label>Название *</x-ui.label>
                <x-ui.input name="name" value="{{ old('name', $company->name) }}" required/>
            </div>
            <div>
                <x-ui.label>Email</x-ui.label>
                <x-ui.input type="email" name="email" value="{{ old('email', $company->email) }}"/>
            </div>
            <div>
                <x-ui.label>Телефон</x-ui.label>
                <x-ui.input name="phone" value="{{ old('phone', $company->phone) }}"/>
            </div>
            <div>
                <x-ui.label>Сайт</x-ui.label>
                <x-ui.input name="website" value="{{ old('website', $company->website) }}"/>
            </div>
            <div>
                <x-ui.label>ИНН/Номер</x-ui.label>
                <x-ui.input name="tax_number" value="{{ old('tax_number', $company->tax_number) }}"/>
            </div>
            <div>
                <x-ui.label>Город</x-ui.label>
                <x-ui.input name="city" value="{{ old('city', $company->city) }}"/>
            </div>
            <div>
                <x-ui.label>Страна</x-ui.label>
                <x-ui.input name="country" value="{{ old('country', $company->country) }}"/>
            </div>
            <div class="md:col-span-2">
                <x-ui.label>Адрес</x-ui.label>
                <x-ui.input name="address" value="{{ old('address', $company->address) }}"/>
            </div>
            <div class="md:col-span-2">
                <x-ui.label>Заметки</x-ui.label>
                <textarea name="notes" class="w-full rounded-xl border px-3 py-2">{{ old('notes', $company->notes) }}</textarea>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <x-ui.button>Сохранить</x-ui.button>
                <a href="{{ route('companies.show', $company) }}" class="px-4 py-2 rounded-xl border">Отмена</a>

                <form method="post" action="{{ route('companies.destroy', $company) }}" class="ml-auto"
                      onsubmit="return confirm('Удалить компанию? Это действие можно будет отменить только через БД.');">
                    @csrf
                    @method('DELETE')
                    <x-ui.button variant="light">Удалить</x-ui.button>
                </form>
            </div>
        </form>
    </x-ui.card>
@endsection
