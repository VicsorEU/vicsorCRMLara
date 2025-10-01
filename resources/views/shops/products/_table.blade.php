<div class="rounded-2xl border bg-white overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
        <tr>
            <th class="text-left font-medium px-4 py-3 w-20">Изображение</th>
            <th class="text-left font-medium px-4 py-3">Название / Slug</th>
            <th class="text-left font-medium px-4 py-3 w-40">SKU</th>
            <th class="text-left font-medium px-4 py-3 w-40">Обычная цена</th>
            <th class="text-left font-medium px-4 py-3 w-32">Вариативный</th>
            <th class="text-left font-medium px-4 py-3 w-24">Вариаций</th>
            <th class="text-right font-medium px-4 py-3 w-24">Изм.</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($items as $p)
            @php
                $primary = $p->images->firstWhere('is_primary', true) ?? $p->images->first();
            @endphp
            <tr class="border-t hover:bg-slate-50/60">
                <td class="px-4 py-2">
                    @if($primary)
                        <img src="{{ asset('storage/'.$primary->path) }}"
                             class="h-10 w-10 rounded-lg object-cover" alt="">
                    @else
                        <div
                            class="h-10 w-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400">
                            —
                        </div>
                    @endif
                </td>

                <td class="px-4 py-2 align-middle">
                    <div class="font-medium">{{ $p->name }}</div>
                    <div class="text-xs text-slate-500">{{ $p->slug }}</div>
                </td>

                <td class="px-4 py-2 align-middle">{{ $p->sku ?: '—' }}</td>

                <td class="px-4 py-2 align-middle">
                    {{ number_format((float)$p->price_regular, 2, '.', ' ') }}
                </td>

                <td class="px-4 py-2 align-middle">{{ $p->is_variable ? 'Да' : 'Нет' }}</td>

                <td class="px-4 py-2 align-middle">{{ $p->variations_count }}</td>

                <td class="px-4 py-2 align-middle text-right">
                    <a href="{{ route('shops.product.edit', ['product' => $p, 'section' => 'products']) }}"
                       class="text-indigo-600 hover:text-indigo-800">
                        Изменить
                    </a>
                </td>
            </tr>
        @empty
            <tr class="border-t">
                <td colspan="7" class="px-4 py-6 text-center text-slate-500">
                    Ничего не найдено.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $items->links() }}
</div>
