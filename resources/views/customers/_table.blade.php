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

<div class="mt-4">
    @if ($items->lastPage() > 1)
        <nav class="pagination flex gap-2">
            @for ($i = 1; $i <= $items->lastPage(); $i++)
                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                   class="px-3 py-1 border rounded @if($i == $items->currentPage()) bg-blue-500 text-white @endif">
                    {{ $i }}
                </a>
            @endfor
        </nav>
    @endif
</div>
