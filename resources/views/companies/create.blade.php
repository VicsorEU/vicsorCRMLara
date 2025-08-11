@extends('layouts.app')
@section('title','Создать компанию — VicsorCRM')
@section('page_title','Новая компания')
@section('content')
    <x-ui.card class="p-6 max-w-3xl">
        <form method="post" action="{{ route('companies.store') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <div class="md:col-span-2">
                <x-ui.label>Название *</x-ui.label>
                <x-ui.input name="name" value="{{ old('name') }}" required/>
            </div>
            <div>
                <x-ui.label>Email</x-ui.label>
                <x-ui.input type="email" name="email" value="{{ old('email') }}"/>
            </div>
            <div>
                <x-ui.label>Телефон</x-ui.label>
                <x-ui.input name="phone" value="{{ old('phone') }}"/>
            </div>
            <div>
                <x-ui.label>Сайт</x-ui.label>
                <x-ui.input name="website" value="{{ old('website') }}"/>
            </div>
            <div>
                <x-ui.label>ИНН/Номер</x-ui.label>
                <x-ui.input name="tax_number" value="{{ old('tax_number') }}"/>
            </div>
            <div>
                <x-ui.label>Город</x-ui.label>
                <x-ui.input name="city" value="{{ old('city') }}"/>
            </div>
            <div>
                <x-ui.label>Страна</x-ui.label>
                <x-ui.input name="country" value="{{ old('country') }}"/>
            </div>
            <div class="md:col-span-2">
                <x-ui.label>Адрес</x-ui.label>
                <x-ui.input name="address" value="{{ old('address') }}"/>
            </div>
            <div class="md:col-span-2">
                <x-ui.label>Заметки</x-ui.label>
                <textarea name="notes" class="w-full rounded-xl border px-3 py-2">{{ old('notes') }}</textarea>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <x-ui.button>Сохранить</x-ui.button>
                <a href="{{ route('companies.index') }}" class="px-4 py-2 rounded-xl border">Отмена</a>
            </div>
        </form>
    </x-ui.card>
@endsection
