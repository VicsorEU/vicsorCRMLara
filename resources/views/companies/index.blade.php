@extends('layouts.app')
@section('title','Компании — VicsorCRM')
@section('page_title','Компании')
@section('page_actions')
    <a href="{{ route('companies.create') }}" class="text-brand-600 hover:underline">+ Создать</a>
@endsection

@section('content')
    <x-ui.card class="p-4">
        <form method="get" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input name="search" value="{{ $search }}" placeholder="Поиск по названию, email, телефону"/>
                <x-ui.button variant="light">Найти</x-ui.button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="text-left text-slate-500">
                    <th class="py-2 pr-4">Название</th>
                    <th class="py-2 pr-4">Контакты</th>
                    <th class="py-2 pr-4">Email</th>
                    <th class="py-2 pr-4">Телефон</th>
                    <th class="py-2 pr-4"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $it)
                    <tr class="border-t">
                        <td class="py-2 pr-4"><a class="text-brand-600" href="{{ route('companies.show',$it) }}">{{ $it->name }}</a></td>
                        <td class="py-2 pr-4">{{ $it->contacts_count }}</td>
                        <td class="py-2 pr-4">{{ $it->email }}</td>
                        <td class="py-2 pr-4">{{ $it->phone }}</td>
                        <td class="py-2 text-right">
                            <a href="{{ route('companies.edit',$it) }}" class="text-slate-500 hover:text-slate-800">Изм.</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $items->links() }}</div>
    </x-ui.card>
@endsection
