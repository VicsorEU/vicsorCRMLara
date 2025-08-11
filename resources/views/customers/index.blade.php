@extends('layouts.app')
@section('title','Покупатели — VicsorCRM')
@section('page_title','Покупатели')
@section('page_actions')
    <a href="{{ route('customers.create') }}" class="text-brand-600 hover:underline">+ Добавить</a>
@endsection

@section('content')
    <x-ui.card class="p-4">
        <form method="get" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input name="search" value="{{ $search }}" placeholder="Поиск по имени, email, телефону"/>
                <x-ui.button variant="light">Найти</x-ui.button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-slate-500">
                    <th class="py-2 pr-4">Имя</th>
                    <th class="py-2 pr-4">Email</th>
                    <th class="py-2 pr-4">Телефон</th>
                    <th class="py-2 pr-4">Менеджер</th>
                    <th class="py-2 pr-4">Адрес</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @foreach($items as $it)
                    <tr class="border-t">
                        <td class="py-2 pr-4">
                            <a class="text-brand-600" href="{{ route('customers.show',$it) }}">{{ $it->full_name }}</a>
                        </td>
                        <td class="py-2 pr-4">{{ optional($it->emails->first())->value ?: '—' }}</td>
                        <td class="py-2 pr-4">{{ optional($it->phones->first())->value ?: '—' }}</td>
                        <td class="py-2 pr-4">{{ optional($it->manager)->name ?: '—' }}</td>
                        <td class="py-2 pr-4">{{ optional($it->defaultAddress)->oneLine() ?: '—' }}</td>
                        <td class="py-2 text-right">
                            <a href="{{ route('customers.edit',$it) }}" class="text-slate-500 hover:text-slate-800">Изм.</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $items->links() }}</div>
    </x-ui.card>
@endsection
