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
        @forelse($roots as $node)
            @include('shops.categories._row', ['node'=>$node, 'groups'=>$groups, 'level'=>0])
        @empty
            <tr>
                <td colspan="5" class="py-6 text-center text-slate-400">Пока нет категорий</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if ($items->lastPage() > 1)
    <nav class="flex gap-2">
        @for ($i = 1; $i <= $items->lastPage(); $i++)
            <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
               class="page-link px-3 py-1 border rounded transition
                      hover:bg-blue-100
                      @if($i == $items->currentPage()) bg-blue-500 text-white font-semibold @else text-slate-600 @endif">
                {{ $i }}
            </a>
        @endfor
    </nav>
@endif
