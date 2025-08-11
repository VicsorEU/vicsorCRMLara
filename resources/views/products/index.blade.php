@extends('layouts.app') {{-- если у тебя другой лейаут (например <x-app-layout>), см. примечание ниже --}}

@section('content')
    <div class="p-6">
        <div class="flex items-center justify-between mb-5">
            <h1 class="text-2xl font-semibold">Товары</h1>
            <a href="{{ route('products.create') }}"
               class="px-4 py-2 rounded-xl border bg-white hover:bg-slate-50">
                + Новый товар
            </a>
        </div>

        <form method="get" class="mb-4">
            <div class="flex gap-2">
                <input type="text" name="q" value="{{ $q ?? '' }}"
                       placeholder="Поиск по имени / SKU / slug"
                       class="w-full md:w-80 rounded-xl border px-3 py-2 bg-white">
                <button class="px-4 py-2 rounded-xl border bg-white hover:bg-slate-50">
                    Искать
                </button>
            </div>
        </form>

        <div class="bg-white rounded-2xl border overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Название / Slug</th>
                    <th class="px-4 py-3 text-left">SKU</th>
                    <th class="px-4 py-3 text-left">Обычная цена</th>
                    <th class="px-4 py-3 text-left">Вариативный</th>
                    <th class="px-4 py-3 text-left">Вариаций</th>
                    <th class="px-4 py-3"></th>
                </tr>
                </thead>
                <tbody>
                @forelse($products as $p)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $p->id }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $p->name }}</div>
                            <div class="text-xs text-slate-500">{{ $p->slug }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $p->sku }}</td>
                        <td class="px-4 py-3">{{ number_format((float) $p->price_regular, 2, '.', ' ') }}</td>
                        <td class="px-4 py-3">{{ $p->is_variable ? 'Да' : 'Нет' }}</td>
                        <td class="px-4 py-3">{{ $p->variations_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('products.edit', $p) }}" class="text-indigo-600 hover:underline">Изменить</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                            Пока нет товаров — создайте первый.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
@endsection
