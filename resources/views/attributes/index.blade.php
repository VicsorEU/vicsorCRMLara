@extends('layouts.app')
@section('title','Атрибуты — VicsorCRM')
@section('page_title','Атрибуты')
@section('page_actions')
    <a href="{{ route('attributes.create') }}" class="text-brand-600 hover:underline">+ Новый атрибут</a>
@endsection

@section('content')
    <x-ui.card class="p-4">
        <form method="get" class="mb-4">
            <div class="flex gap-2">
                <x-ui.input name="search" value="{{ $search }}" placeholder="Поиск по названию/слагу"/>
                <x-ui.button variant="light">Искать</x-ui.button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="text-left text-slate-500">
                    <th class="py-2 pr-4">Название</th>
                    <th class="py-2 pr-4">Слаг</th>
                    <th class="py-2 pr-4">Значений</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $it)
                    <tr class="border-t">
                        <td class="py-2 pr-4 font-medium">{{ $it->name }}</td>
                        <td class="py-2 pr-4 text-slate-500">{{ $it->slug }}</td>
                        <td class="py-2 pr-4">{{ $it->values_count }}</td>
                        <td class="py-2 text-right">
                            <a href="{{ route('attributes.edit',$it) }}" class="text-slate-500 hover:text-slate-800">Изм.</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $items->links() }}</div>
    </x-ui.card>
@endsection
