@extends('layouts.app')
@section('title','Категории — VicsorCRM')
@section('page_title','Категории')
@section('page_actions')
    <a href="{{ route('categories.create') }}" class="text-brand-600 hover:underline">+ Создать</a>
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
                    <th class="py-2 pr-4">Изображение</th>
                    <th class="py-2 pr-4">Название</th>
                    <th class="py-2 pr-4">Слаг</th>
                    <th class="py-2 pr-4">Родитель</th>
                    <th class="py-2 pr-4"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $it)
                    <tr class="border-t">
                        <td class="py-2 pr-4">
                            @if($it->image_url)
                                <img src="{{ $it->image_url }}" alt="" class="h-10 w-10 object-cover rounded-lg border">
                            @else
                                <div class="h-10 w-10 rounded-lg bg-slate-100 border"></div>
                            @endif
                        </td>
                        <td class="py-2 pr-4 font-medium">{{ $it->name }}</td>
                        <td class="py-2 pr-4 text-slate-500">{{ $it->slug }}</td>
                        <td class="py-2 pr-4">{{ optional($it->parent)->name ?: '—' }}</td>
                        <td class="py-2 text-right">
                            <a href="{{ route('categories.edit',$it) }}" class="text-slate-500 hover:text-slate-800">Изм.</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $items->links() }}</div>
    </x-ui.card>
@endsection
