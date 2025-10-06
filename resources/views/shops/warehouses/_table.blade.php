@php
    $groups = $items->groupBy(fn($w) => $w->parent_id ?? 0);
    $roots  = $groups->get(0, collect());
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
        <tr class="text-left text-slate-500">
            <th class="py-2 pr-4">Название</th>
            <th class="py-2 pr-4">Код</th>
            <th class="py-2 pr-4">Адрес</th>
            <th class="py-2 pr-4">Активен</th>
            <th class="py-2 pr-4"></th>
        </tr>
        </thead>
        <tbody id="wh-table-body">
        @forelse($roots as $node)
            @include('shops.warehouses._row', ['node'=>$node, 'groups'=>$groups, 'level'=>0])
        @empty
            <tr>
                <td colspan="5" class="text-center text-slate-400 py-6">Пока нет складов</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->lastPage() > 1)
    <nav class="flex gap-2 mt-4">
        @for ($i = 1; $i <= $items->lastPage(); $i++)
            <a href="{{ request()->fullUrlWithQuery(['page'=>$i]) }}" class="page-link px-3 py-1 border rounded
               hover:bg-blue-100
               @if($i == $items->currentPage()) bg-blue-500 text-white font-semibold @else text-slate-600 @endif">
                {{ $i }}
            </a>
        @endfor
    </nav>
@endif
