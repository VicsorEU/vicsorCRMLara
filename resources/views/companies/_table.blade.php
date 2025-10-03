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

<div class="mt-4">
    @if ($items->lastPage() > 1)
        <nav class="flex gap-2">
            @for ($i = 1; $i <= $items->lastPage(); $i++)
                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                   class="px-3 py-1 border rounded transition
                          hover:bg-blue-100
                          @if($i == $items->currentPage()) bg-blue-500 text-white font-semibold @else text-slate-600 @endif">
                    {{ $i }}
                </a>
            @endfor
        </nav>
    @endif
</div>

