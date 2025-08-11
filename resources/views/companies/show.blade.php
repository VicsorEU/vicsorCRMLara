@extends('layouts.app')
@section('title', $company->name.' — VicsorCRM')
@section('page_title', $company->name)
@section('page_actions')
    <a class="text-brand-600 hover:underline" href="{{ route('companies.edit',$company) }}">Изменить</a>
@endsection

@section('content')
    <div class="grid md:grid-cols-3 gap-6">
        <x-ui.card class="p-6 md:col-span-2">
            <div class="grid md:grid-cols-2 gap-4">
                <div><div class="text-xs text-slate-500">Email</div><div>{{ $company->email ?: '—' }}</div></div>
                <div><div class="text-xs text-slate-500">Телефон</div><div>{{ $company->phone ?: '—' }}</div></div>
                <div><div class="text-xs text-slate-500">Сайт</div><div>{{ $company->website ?: '—' }}</div></div>
                <div><div class="text-xs text-slate-500">ИНН</div><div>{{ $company->tax_number ?: '—' }}</div></div>
                <div class="md:col-span-2"><div class="text-xs text-slate-500">Адрес</div><div>{{ $company->address ?: '—' }}</div></div>
                <div class="md:col-span-2"><div class="text-xs text-slate-500">Заметки</div><div>{{ $company->notes ?: '—' }}</div></div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-6">
            <div class="font-medium mb-3">Контакты</div>
            <ul class="space-y-2">
                @forelse($company->contacts as $c)
                    <li>
                        <a href="{{ route('contacts.show',$c) }}" class="text-brand-600 hover:underline">{{ $c->full_name }}</a>
                        <div class="text-xs text-slate-500">{{ $c->email }} @if($c->phone) • {{ $c->phone }} @endif</div>
                    </li>
                @empty
                    <li class="text-slate-500 text-sm">Нет контактов</li>
                @endforelse
            </ul>
        </x-ui.card>
    </div>
@endsection
